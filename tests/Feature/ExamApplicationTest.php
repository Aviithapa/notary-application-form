<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamApplicationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function applicant(bool $withForm = true): User
    {
        $user = User::create(['email' => 'app' . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', RoleEnum::APPLICANT)->value('id'));
        if ($withForm) {
            $user->applicantProfile()->create(['name' => 'Test Applicant']);
            $user->qualifications()->create(['university_name' => 'TU', 'level' => 'LLB']);
        }

        return $user;
    }

    /** Applications open 2026-11-01..2026-11-15, double fee until 2026-11-22. */
    private function exam(array $service = [], array $exam = []): Exam
    {
        $service = Service::create(array_merge([
            'name' => 'Licence', 'name_np' => 'इजाजतपत्र', 'purpose' => 'new_license', 'icon' => 'bi-award',
            'is_fee_based' => true, 'fee_amount' => 2500, 'requires_exam' => true,
            'requires_application_form' => true, 'is_active' => true,
        ], $service));

        return $service->exams()->create(array_merge([
            'title' => 'Notary exam', 'status' => 'published',
            'application_start_ad' => '2026-11-01', 'application_start_bs' => '2083-07-15',
            'application_end_ad' => '2026-11-15', 'application_end_bs' => '2083-07-29',
            'double_fee_end_ad' => '2026-11-22', 'double_fee_end_bs' => '2083-08-06',
        ], $exam));
    }

    /** Freezes "today" in Nepal time. */
    private function today(string $date): void
    {
        Carbon::setTestNow(Carbon::parse("$date 12:00", config('app.business_timezone')));
    }

    public function test_phase_and_fee_follow_the_application_period(): void
    {
        $exam = $this->exam();

        $this->assertSame(Exam::PHASE_UPCOMING, $exam->phase('2026-10-31'));
        $this->assertSame(Exam::PHASE_OPEN, $exam->phase('2026-11-01'));
        $this->assertSame('2500.00', $exam->currentFee('2026-11-15'));
        $this->assertSame(Exam::PHASE_DOUBLE_FEE, $exam->phase('2026-11-16'));
        $this->assertSame('5000.00', $exam->currentFee('2026-11-22'));
        $this->assertSame(Exam::PHASE_CLOSED, $exam->phase('2026-11-23'));

        $exam->status = 'draft';
        $this->assertSame(Exam::PHASE_CLOSED, $exam->phase('2026-11-05'));
    }

    public function test_deadline_uses_nepal_time(): void
    {
        // 23:00 UTC on the 22nd is already the 23rd in Nepal, so the double-fee window has closed
        Carbon::setTestNow(Carbon::parse('2026-11-22 23:00', 'UTC'));

        $this->assertSame('2026-11-23', Exam::today());
        $this->assertFalse($this->exam()->isAcceptingApplications());
    }

    public function test_applicant_applies_at_normal_fee_once(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam();
        $user = $this->applicant();
        $this->actingAs($user);

        $this->get(route('applicant.exams'))->assertOk()->assertSee('Notary exam')->assertSee('2,500.00');

        $this->post(route('applicant.exams.apply', $exam))->assertRedirect(route('applicant.applications'));
        $application = $user->examApplications()->firstOrFail();
        $this->assertSame('2500.00', $application->fee_amount);
        $this->assertFalse($application->is_double_fee);
        $this->assertMatchesRegularExpression('/^NPC-\d{4}-\d{6}$/', $application->application_number);

        $this->post(route('applicant.exams.apply', $exam))->assertSessionHas('error');
        $this->assertSame(1, $user->examApplications()->count());

        $this->get(route('applicant.applications'))->assertOk()->assertSee($application->application_number);
        $this->get(route('dashboard'))->assertOk()->assertSee(route('applicant.applications'), false);
    }

    public function test_double_fee_period_charges_twice(): void
    {
        $this->today('2026-11-18');
        $exam = $this->exam();
        $user = $this->applicant();

        $this->actingAs($user)->get(route('applicant.exams'))->assertSee('5,000.00');
        $this->post(route('applicant.exams.apply', $exam));

        $application = $user->examApplications()->firstOrFail();
        $this->assertSame('5000.00', $application->fee_amount);
        $this->assertTrue($application->is_double_fee);
    }

    public function test_cannot_apply_outside_the_period_or_to_unpublished_exams(): void
    {
        $user = $this->applicant();
        $this->actingAs($user);

        $this->today('2026-11-30');
        $this->post(route('applicant.exams.apply', $this->exam()))->assertSessionHas('error');

        $this->today('2026-11-10');
        $this->post(route('applicant.exams.apply', $this->exam([], ['status' => 'draft'])))->assertSessionHas('error');
        $this->post(route('applicant.exams.apply', $this->exam(['is_active' => false])))->assertSessionHas('error');

        $this->assertSame(0, $user->examApplications()->count());
    }

    public function test_application_form_must_be_complete_when_the_service_requires_it(): void
    {
        $this->today('2026-11-10');
        $user = $this->applicant(withForm: false);
        $this->actingAs($user);

        $this->post(route('applicant.exams.apply', $this->exam()))->assertRedirect(route('applicant.personal'));
        $this->assertSame(0, $user->examApplications()->count());

        // A free service that does not need the form can be applied to straight away
        $this->post(route('applicant.exams.apply', $this->exam(['is_fee_based' => false, 'fee_amount' => null, 'requires_application_form' => false])));
        $application = $user->examApplications()->firstOrFail();
        $this->assertNull($application->fee_amount);
    }

    public function test_admin_sees_applications_and_cannot_delete_exam_with_applications(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam();
        $applicant = $this->applicant();
        $this->actingAs($applicant)->post(route('applicant.exams.apply', $exam));

        $admin = User::create(['email' => 'adm' . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $admin->roles()->attach(Role::where('name', RoleEnum::ADMIN)->value('id'));
        $this->actingAs($admin);

        $this->get(route('exams.index'))->assertOk()->assertSee(route('exams.applications', $exam), false);
        $this->get(route('exams.applications', $exam))->assertOk()
            ->assertSee($applicant->email)->assertSee('Test Applicant')
            ->assertSee($applicant->examApplications()->value('application_number'));

        $this->delete(route('exams.destroy', $exam))->assertSessionHas('error');
        $this->assertModelExists($exam);
    }
}
