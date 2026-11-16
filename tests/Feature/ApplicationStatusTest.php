<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApplicationStatusTest extends TestCase
{
    use DatabaseTransactions;

    private Exam $exam;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $service = Service::create([
            'name' => 'Licence', 'name_np' => 'इजाजतपत्र', 'purpose' => 'new_license', 'icon' => 'bi-award',
            'is_fee_based' => true, 'fee_amount' => 2500, 'requires_exam' => true, 'is_active' => true,
        ]);
        $this->exam = $service->exams()->create([
            'title' => 'Notary exam', 'status' => 'published',
            'application_start_ad' => '2026-11-01', 'application_start_bs' => '2083-07-15',
            'application_end_ad' => '2026-11-15', 'application_end_bs' => '2083-07-29',
            'exam_date_ad' => '2026-12-20', 'exam_date_bs' => '2083-09-05', 'start_time' => '10:00', 'venue' => 'Council hall',
        ]);
        $this->admin = $this->userWithRole(RoleEnum::ADMIN);
    }

    private function userWithRole(string $role): User
    {
        $user = User::create(['email' => $role . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', $role)->value('id'));

        return $user;
    }

    private function application(): ExamApplication
    {
        $applicant = $this->userWithRole(RoleEnum::APPLICANT);
        $applicant->applicantProfile()->create(['name' => 'Applicant ' . $applicant->id]);

        return $applicant->examApplications()->create(['exam_id' => $this->exam->id, 'fee_amount' => 2500, 'paid_amount' => 2500]);
    }

    private function move(ExamApplication $application, string $status, ?string $remarks = null)
    {
        return $this->actingAs($this->admin)->patch(
            route('exams.applications.status', [$this->exam, $application]),
            ['status' => $status, 'remarks' => $remarks],
        );
    }

    public function test_full_workflow_assigns_roll_numbers_and_logs_history(): void
    {
        $first = $this->application();
        $second = $this->application();
        $this->assertSame(ExamApplication::PENDING, $first->fresh()->status);

        foreach ([$first, $second] as $application) {
            foreach ([ExamApplication::APPROVED, ExamApplication::READY_FOR_ADMIT_CARD, ExamApplication::ADMIT_CARD_GENERATED] as $status) {
                $this->move($application, $status)->assertSessionHas('success');
            }
        }

        $this->assertSame(1, $first->fresh()->roll_number);
        $this->assertSame(2, $second->fresh()->roll_number);
        $this->assertSame('00001', $first->fresh()->rollNumberLabel());
        $this->assertNotNull($first->fresh()->admit_card_generated_at);

        $this->move($first, ExamApplication::PASSED)->assertSessionHas('success');
        $this->move($second, ExamApplication::FAILED)->assertSessionHas('success');
        // Results can be corrected
        $this->move($second, ExamApplication::PASSED)->assertSessionHas('success');
        $this->assertSame(ExamApplication::PASSED, $second->fresh()->status);

        // submitted + approved + ready + generated + passed
        $this->assertSame(5, $first->statusLogs()->count());
        $submitted = $first->statusLogs()->reorder('id')->first();
        $this->assertSame([null, 'pending'], [$submitted->from_status, $submitted->to_status]);

        $this->get(route('exams.applications.show', [$this->exam, $first]))->assertOk()
            ->assertSee(__('Application status'))->assertSee('00001')->assertSee(route('exams.applications.admit-card', [$this->exam, $first]), false);
    }

    public function test_steps_cannot_be_skipped(): void
    {
        $application = $this->application();

        $this->move($application, ExamApplication::PASSED)->assertSessionHas('error');
        $this->move($application, ExamApplication::ADMIT_CARD_GENERATED)->assertSessionHas('error');
        $this->assertSame(ExamApplication::PENDING, $application->fresh()->status);
        $this->assertNull($application->fresh()->roll_number);
    }

    public function test_rejection_needs_a_reason_that_the_applicant_sees(): void
    {
        $application = $this->application();

        $this->move($application, ExamApplication::REJECTED)->assertSessionHasErrors('remarks');
        $this->assertSame(ExamApplication::PENDING, $application->fresh()->status);

        $this->move($application, ExamApplication::REJECTED, 'Voucher amount does not match.')->assertSessionHas('success');
        $this->assertSame(ExamApplication::REJECTED, $application->fresh()->status);

        $this->actingAs($application->user)->get(route('applicant.applications'))
            ->assertSee(__('Rejected'))->assertSee('Voucher amount does not match.');
        $this->get(route('applicant.applications.show', $application))
            ->assertSee('Voucher amount does not match.')
            ->assertDontSee(__('Generate admit card')); // no admin actions for the applicant

        // A rejected application can be reopened
        $this->move($application, ExamApplication::PENDING)->assertSessionHas('success');
        $this->assertSame(ExamApplication::PENDING, $application->fresh()->status);
    }

    public function test_bulk_change_skips_applications_that_cannot_move(): void
    {
        $pendingA = $this->application();
        $pendingB = $this->application();
        $rejected = $this->application();
        $rejected->changeStatus(ExamApplication::REJECTED, 'Incomplete', $this->admin);

        $this->actingAs($this->admin)->patch(route('applications.bulk-status'), [
            'status' => ExamApplication::APPROVED,
            'applications' => [$pendingA->id, $pendingB->id, $rejected->id],
        ])->assertSessionHas('success', fn ($message) => str_contains($message, '2') && str_contains($message, '1'));

        $this->assertSame(ExamApplication::APPROVED, $pendingA->fresh()->status);
        $this->assertSame(ExamApplication::APPROVED, $pendingB->fresh()->status);
        $this->assertSame(ExamApplication::REJECTED, $rejected->fresh()->status);

        $this->get(route('exams.applications', [$this->exam, 'status' => ExamApplication::APPROVED]))->assertOk()
            ->assertSee($pendingA->application_number)->assertDontSee($rejected->application_number);
    }

    public function test_admit_card_only_after_generation_and_only_for_owner_or_admin(): void
    {
        $application = $this->application();
        $owner = $application->user;

        $this->actingAs($owner)->get(route('applicant.applications.admit-card', $application))->assertNotFound();
        $this->actingAs($this->admin)->get(route('exams.applications.admit-card', [$this->exam, $application]))->assertNotFound();

        foreach ([ExamApplication::APPROVED, ExamApplication::READY_FOR_ADMIT_CARD, ExamApplication::ADMIT_CARD_GENERATED] as $status) {
            $application->changeStatus($status, null, $this->admin);
        }

        $this->actingAs($owner)->get(route('applicant.applications'))->assertSee(route('applicant.applications.admit-card', $application), false);
        $this->get(route('applicant.applications.admit-card', $application))->assertOk()
            ->assertSee('प्रवेश पत्र')->assertSee('00001')->assertSee('Applicant ' . $owner->id)->assertSee('2083-09-05')->assertSee('Council hall');
        $this->actingAs($this->admin)->get(route('exams.applications.admit-card', [$this->exam, $application]))->assertOk();
        $this->actingAs($this->userWithRole(RoleEnum::APPLICANT))->get(route('applicant.applications.admit-card', $application))->assertNotFound();
    }

    public function test_sidebar_lists_services_with_pending_counts_and_links_to_their_applications(): void
    {
        $licenceA = $this->application();
        $licenceB = $this->application();
        $this->application()->changeStatus(ExamApplication::APPROVED, null, $this->admin); // not pending

        $renewal = Service::create([
            'name' => 'Licence renewal', 'name_np' => 'नवीकरण', 'purpose' => 'renewal', 'icon' => 'bi-arrow-repeat',
            'requires_exam' => true, 'is_active' => true,
        ]);
        $renewalExam = $renewal->exams()->create(['title' => 'Renewal call', 'status' => 'published']);
        $applicant = $this->userWithRole(RoleEnum::APPLICANT);
        $renewalApplication = $applicant->examApplications()->create(['exam_id' => $renewalExam->id]);
        Service::create(['name' => 'Service without exams', 'name_np' => 'x', 'purpose' => 'other', 'icon' => 'bi-briefcase']);

        $this->actingAs($this->admin);
        // Read the badges on a page whose content does not link to these lists itself
        $sidebar = $this->get(route('change.password'))->assertOk();
        $licenceUrl = route('applications.index', ['service' => $this->exam->service_id]);
        $renewalUrl = route('applications.index', ['service' => $renewal->id]);
        $sidebar->assertSee($licenceUrl, false)->assertSee($renewalUrl, false)->assertDontSee('Service without exams');
        // Badges: licence 2 pending, renewal 1, all services = every pending application
        $html = $sidebar->getContent();
        $this->assertMatchesRegularExpression('#' . preg_quote(e($licenceUrl), '#') . '"[^>]*>.*?rounded-pill[^>]*>2<#s', $html);
        $this->assertMatchesRegularExpression('#' . preg_quote(e($renewalUrl), '#') . '"[^>]*>.*?rounded-pill[^>]*>1<#s', $html);
        $this->assertMatchesRegularExpression('#' . preg_quote(e(route('applications.index')), '#') . '"[^>]*>.*?rounded-pill[^>]*>' . ExamApplication::where('status', ExamApplication::PENDING)->count() . '<#s', $html);

        // Service page lists only that service's applications, with status tabs
        $this->get($licenceUrl)->assertOk()
            ->assertSee($licenceA->application_number)->assertSee($licenceB->application_number)
            ->assertDontSee($renewalApplication->application_number)
            ->assertSee(e(route('applications.index', ['service' => $this->exam->service_id, 'status' => ExamApplication::PENDING])), false);
        $this->get(route('applications.index', ['service' => $this->exam->service_id, 'status' => ExamApplication::APPROVED]))->assertOk()
            ->assertDontSee($licenceA->application_number);
        $this->get(route('applications.index'))->assertOk()->assertSee($renewalApplication->application_number)->assertSee('Renewal call');

        // Bulk changes work from this page too, and the badge goes down
        $this->from($licenceUrl)->patch(route('applications.bulk-status'), [
            'status' => ExamApplication::APPROVED, 'applications' => [$licenceA->id],
        ])->assertRedirect($licenceUrl);
        $this->assertSame(ExamApplication::APPROVED, $licenceA->fresh()->status);
        $this->assertMatchesRegularExpression('#' . preg_quote(e($licenceUrl), '#') . '"[^>]*>.*?rounded-pill[^>]*>1<#s', $this->get(route('change.password'))->getContent());
    }

    public function test_service_without_exam_only_uses_pending_approved_rejected(): void
    {
        $service = Service::create([
            'name' => 'Licence renewal', 'name_np' => 'नवीकरण', 'purpose' => 'renewal', 'icon' => 'bi-arrow-repeat',
            'requires_exam' => false, 'is_active' => true,
        ]);
        $call = $service->exams()->create(['title' => 'Renewal call', 'status' => 'published']);
        $application = $this->userWithRole(RoleEnum::APPLICANT)->examApplications()->create(['exam_id' => $call->id]);

        $this->assertSame([ExamApplication::APPROVED, ExamApplication::REJECTED], $application->nextStatuses());

        // List: only Pending / Approved / Rejected tabs and bulk options
        $this->actingAs($this->admin);
        foreach ([route('applications.index', ['service' => $service->id]), route('exams.applications', $call)] as $url) {
            $page = $this->get($url)->assertOk()->assertSee(__('Pending'))->assertSee(__('Approved'))->assertSee(__('Rejected'))
                ->assertDontSee(__('Ready for admit card'))->assertDontSee(__('Admit card generated'))
                ->assertDontSee('>' . __('Passed'), false)->assertDontSee('>' . __('Failed'), false);
        }

        // Approved is the end of the workflow (it can only still be rejected): no admit card, no result
        $this->patch(route('exams.applications.status', [$call, $application]), ['status' => ExamApplication::APPROVED])->assertSessionHas('success');
        $this->assertSame([ExamApplication::REJECTED], $application->fresh()->nextStatuses());
        $this->get(route('exams.applications.show', [$call, $application]))->assertOk()
            ->assertDontSee(__('Mark ready for admit card'))->assertDontSee(__('Ready for admit card'));
        $this->patch(route('exams.applications.status', [$call, $application]), ['status' => ExamApplication::READY_FOR_ADMIT_CARD])->assertSessionHas('error');

        // Bulk skips statuses outside the workflow
        $other = $this->userWithRole(RoleEnum::APPLICANT)->examApplications()->create(['exam_id' => $call->id]);
        $other->changeStatus(ExamApplication::APPROVED, null, $this->admin);
        $this->patch(route('applications.bulk-status'), ['status' => ExamApplication::PASSED, 'applications' => [$other->id]])->assertSessionHas('error');
        $this->assertSame(ExamApplication::APPROVED, $other->fresh()->status);
    }

    public function test_admin_dashboard_summarises_applications(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00', config('app.business_timezone')));
        $pending = $this->application();
        $approved = $this->application();
        $approved->changeStatus(ExamApplication::APPROVED, null, $this->admin);

        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()
            ->assertSee(__('Needs attention'))
            ->assertSee(e(route('applications.index', ['service' => $this->exam->service_id, 'status' => ExamApplication::PENDING])), false)
            ->assertSee(__('Open for applications'))->assertSee('Notary exam')        // open call
            ->assertSee(__('Upcoming exams'))->assertSee('2083-09')                    // exam date (day and month shown apart)
            ->assertSee($pending->application_number)->assertSee($approved->application_number) // recent
            ->assertSee(__('Applications by status'));
        Carbon::setTestNow();
    }

    public function test_applicants_cannot_change_status(): void
    {
        $application = $this->application();

        $this->actingAs($application->user)
            ->patch(route('exams.applications.status', [$this->exam, $application]), ['status' => ExamApplication::APPROVED])
            ->assertRedirect('/dashboard');
        $this->assertSame(ExamApplication::PENDING, $application->fresh()->status);
    }
}
