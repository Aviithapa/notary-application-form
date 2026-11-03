<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamApplicationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
    }

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

    /** A complete application form: voucher, amount paid, signature and both thumbprints. */
    private function submission(array $overrides = []): array
    {
        return array_merge([
            'paid_amount' => '10000',
            'voucher' => UploadedFile::fake()->image('voucher.jpg'),
            'signature' => UploadedFile::fake()->image('signature.png'),
            'thumb_left' => UploadedFile::fake()->image('left.png'),
            'thumb_right' => UploadedFile::fake()->image('right.png'),
            'declaration' => '1',
        ], $overrides);
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

        $this->post(route('applicant.exams.apply', $exam), $this->submission())->assertRedirect(route('applicant.applications'));
        $application = $user->examApplications()->firstOrFail();
        $this->assertSame('2500.00', $application->fee_amount);
        $this->assertFalse($application->is_double_fee);
        $this->assertMatchesRegularExpression('/^NPC-\d{4}-\d{6}$/', $application->application_number);

        $this->post(route('applicant.exams.apply', $exam), $this->submission())->assertSessionHas('error');
        $this->assertSame(1, $user->examApplications()->count());

        $this->get(route('applicant.applications'))->assertOk()->assertSee($application->application_number);
        $this->get(route('dashboard'))->assertOk()->assertSee(route('applicant.applications'), false);
    }

    public function test_application_form_requires_payment_proof_signature_and_thumbprints(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam();
        $user = $this->applicant();
        $this->actingAs($user);

        $this->get(route('applicant.exams'))->assertSee(route('applicant.exams.apply.form', $exam), false);
        $this->get(route('applicant.exams.apply.form', $exam))->assertOk()
            ->assertSee('name="voucher"', false)->assertSee('name="paid_amount"', false)
            ->assertSee('name="signature"', false)->assertSee('name="thumb_left"', false)->assertSee('name="thumb_right"', false)
            // "View details" opens the applicant's full form for review before declaring
            ->assertSee('id="application-details"', false)->assertSee('Test Applicant')->assertSee('LLB');

        $this->post(route('applicant.exams.apply', $exam), [])
            ->assertSessionHasErrors(['voucher', 'paid_amount', 'signature', 'thumb_left', 'thumb_right', 'declaration']);
        $this->post(route('applicant.exams.apply', $exam), $this->submission(['paid_amount' => '2000']))
            ->assertSessionHasErrors('paid_amount');
        $this->assertSame(0, $user->examApplications()->count());

        $this->post(route('applicant.exams.apply', $exam), $this->submission(['paid_amount' => '2500']))
            ->assertSessionHasNoErrors()->assertRedirect(route('applicant.applications'));

        $application = $user->examApplications()->firstOrFail();
        $this->assertSame('2500.00', $application->paid_amount);
        foreach (['voucher', 'signature', 'thumb_left', 'thumb_right'] as $collection) {
            $this->assertNotNull($application->getFirstMedia($collection), $collection);
        }

        // Once applied, the form is no longer offered
        $this->get(route('applicant.exams.apply.form', $exam))->assertRedirect(route('applicant.applications'));
    }

    public function test_free_service_needs_no_voucher(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam(['is_fee_based' => false, 'fee_amount' => null]);
        $user = $this->applicant();

        $this->actingAs($user)->get(route('applicant.exams.apply.form', $exam))->assertOk()->assertDontSee('name="voucher"', false);
        $this->post(route('applicant.exams.apply', $exam), $this->submission(['voucher' => null, 'paid_amount' => null]))
            ->assertSessionHasNoErrors();

        $application = $user->examApplications()->firstOrFail();
        $this->assertNull($application->paid_amount);
        $this->assertNull($application->getFirstMedia('voucher'));
        $this->assertNotNull($application->getFirstMedia('signature'));
    }

    public function test_double_fee_period_charges_twice(): void
    {
        $this->today('2026-11-18');
        $exam = $this->exam();
        $user = $this->applicant();

        $this->actingAs($user)->get(route('applicant.exams'))->assertSee('5,000.00');
        $this->post(route('applicant.exams.apply', $exam), $this->submission());

        $application = $user->examApplications()->firstOrFail();
        $this->assertSame('5000.00', $application->fee_amount);
        $this->assertTrue($application->is_double_fee);
    }

    public function test_cannot_apply_outside_the_period_or_to_unpublished_exams(): void
    {
        $user = $this->applicant();
        $this->actingAs($user);

        $this->today('2026-11-30');
        $this->post(route('applicant.exams.apply', $this->exam()), $this->submission())->assertSessionHas('error');

        $this->today('2026-11-10');
        $this->post(route('applicant.exams.apply', $this->exam([], ['status' => 'draft'])), $this->submission())->assertSessionHas('error');
        $this->post(route('applicant.exams.apply', $this->exam(['is_active' => false])), $this->submission())->assertSessionHas('error');

        $this->assertSame(0, $user->examApplications()->count());
    }

    public function test_application_form_must_be_complete_when_the_service_requires_it(): void
    {
        $this->today('2026-11-10');
        $user = $this->applicant(withForm: false);
        $this->actingAs($user);

        $this->post(route('applicant.exams.apply', $this->exam()), $this->submission())->assertRedirect(route('applicant.personal'));
        $this->assertSame(0, $user->examApplications()->count());

        // A free service that does not need the form can be applied to straight away
        $this->post(route('applicant.exams.apply', $this->exam(['is_fee_based' => false, 'fee_amount' => null, 'requires_application_form' => false])), $this->submission());
        $application = $user->examApplications()->firstOrFail();
        $this->assertNull($application->fee_amount);
    }

    public function test_view_application_shows_the_full_form_to_owner_and_admin_only(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam();
        $applicant = $this->applicant();
        $applicant->applicantProfile->update(['name_np' => 'परीक्षण आवेदक', 'citizenship_number' => '12-34-56789', 'dob_ad' => '1990-05-15', 'dob_bs' => '2047-02-01']);
        $applicant->trainings()->create(['training_name' => 'Notary orientation', 'organization' => 'NPC']);
        $this->actingAs($applicant)->post(route('applicant.exams.apply', $exam), $this->submission());
        $application = $applicant->examApplications()->firstOrFail();

        $this->get(route('applicant.applications'))->assertSee(route('applicant.applications.show', $application), false);
        $this->get(route('applicant.applications.show', $application))->assertOk()
            ->assertSee($application->application_number)
            ->assertSee('Test Applicant')->assertSee('परीक्षण आवेदक')->assertSee('12-34-56789')->assertSee('2047-02-01')
            ->assertSee('TU')->assertSee('LLB')
            ->assertSee('Notary orientation')
            ->assertSee(route('lookup.media', $application->getFirstMedia('signature')), false);

        // Another applicant cannot open it
        $this->actingAs($this->applicant())->get(route('applicant.applications.show', $application))->assertNotFound();

        $admin = User::create(['email' => 'adm' . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $admin->roles()->attach(Role::where('name', RoleEnum::ADMIN)->value('id'));
        $this->actingAs($admin);

        $this->get(route('exams.applications', $exam))->assertSee(route('exams.applications.show', [$exam, $application]), false);
        $this->get(route('exams.applications.show', [$exam, $application]))->assertOk()
            ->assertSee('Test Applicant')->assertSee('Notary orientation');

        // The application must belong to the exam in the URL
        $this->get(route('exams.applications.show', [$this->exam(), $application]))->assertNotFound();
    }

    public function test_admin_sees_applications_and_cannot_delete_exam_with_applications(): void
    {
        $this->today('2026-11-10');
        $exam = $this->exam();
        $applicant = $this->applicant();
        $this->actingAs($applicant)->post(route('applicant.exams.apply', $exam), $this->submission());

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
