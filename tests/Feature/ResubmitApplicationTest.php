<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\ExamApplication;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResubmitApplicationTest extends TestCase
{
    use DatabaseTransactions;

    private User $applicant;
    private User $admin;
    private ExamApplication $application;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
        // Applications open 2026-11-01..2026-11-15, double fee until 2026-11-22
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00', config('app.business_timezone')));

        $this->applicant = $this->userWithRole(RoleEnum::APPLICANT);
        $this->applicant->applicantProfile()->create(['name' => 'Wrong Name']);
        $this->applicant->qualifications()->create(['university_name' => 'TU', 'level' => 'LLB']);
        $this->admin = $this->userWithRole(RoleEnum::ADMIN);

        $exam = Service::create([
            'name' => 'Licence', 'name_np' => 'इजाजतपत्र', 'purpose' => 'new_license', 'icon' => 'bi-award',
            'is_fee_based' => true, 'fee_amount' => 2500, 'requires_exam' => true, 'requires_application_form' => true, 'is_active' => true,
        ])->exams()->create([
            'title' => 'Notary exam', 'status' => 'published',
            'application_start_ad' => '2026-11-01', 'application_start_bs' => '2083-07-15',
            'application_end_ad' => '2026-11-15', 'application_end_bs' => '2083-07-29',
            'double_fee_end_ad' => '2026-11-22', 'double_fee_end_bs' => '2083-08-06',
        ]);

        $this->actingAs($this->applicant)->post(route('applicant.exams.apply', $exam), [
            'paid_amount' => '2500',
            'voucher' => UploadedFile::fake()->image('voucher.jpg'),
            'signature' => UploadedFile::fake()->image('signature.png'),
            'thumb_left' => UploadedFile::fake()->image('left.png'),
            'thumb_right' => UploadedFile::fake()->image('right.png'),
            'declaration' => '1',
        ]);
        $this->application = $this->applicant->examApplications()->firstOrFail();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function userWithRole(string $role): User
    {
        $user = User::create(['email' => $role . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', $role)->value('id'));

        return $user;
    }

    private function reject(): void
    {
        $this->application->changeStatus(ExamApplication::REJECTED, 'Name does not match citizenship.', $this->admin);
    }

    public function test_applicant_fixes_details_and_resubmits(): void
    {
        $this->reject();
        $signature = $this->application->getFirstMedia('signature');
        $voucher = $this->application->getFirstMedia('voucher');

        // Guidance and the resubmit button
        $this->get(route('applicant.applications'))->assertSee(route('applicant.applications.resubmit.form', $this->application), false);
        $this->get(route('applicant.applications.show', $this->application))
            ->assertSee(__('How to resubmit'))->assertSee('Name does not match citizenship.');

        // Details are editable while rejected
        $this->post(route('applicant.personal.save'), ['name' => 'Correct Name'])->assertSessionHasNoErrors();

        // Form shows the reason and the current files, which are optional now
        $this->get(route('applicant.applications.resubmit.form', $this->application))->assertOk()
            ->assertSee('Name does not match citizenship.')
            ->assertSee(__('Leave empty to keep the current file.'))
            ->assertSee(route('lookup.media', $signature), false);

        // Resubmit keeping the voucher and thumbprints, replacing the signature
        $this->put(route('applicant.applications.resubmit', $this->application), [
            'paid_amount' => '2500',
            'signature' => UploadedFile::fake()->image('new-signature.png'),
            'declaration' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('applicant.applications.show', $this->application));

        $application = $this->application->fresh();
        $this->assertSame(ExamApplication::PENDING, $application->status);
        $this->assertSame('2500.00', $application->fee_amount); // fee unchanged
        $this->assertSame($voucher->id, $application->getFirstMedia('voucher')->id);
        $this->assertNotSame($signature->id, $application->getFirstMedia('signature')->id);
        $this->assertSame('new-signature.png', $application->getFirstMedia('signature')->file_name);
        $this->assertSame(1, $this->applicant->examApplications()->count()); // same application, not a new one

        $log = $application->statusLogs()->first();
        $this->assertSame([ExamApplication::REJECTED, ExamApplication::PENDING, $this->applicant->id], [$log->from_status, $log->to_status, $log->changed_by]);
        $this->assertSame('Correct Name', $this->applicant->applicantProfile->fresh()->name);
    }

    public function test_resubmission_still_needs_declaration_and_full_fee(): void
    {
        $this->reject();

        $this->put(route('applicant.applications.resubmit', $this->application), ['paid_amount' => '1000'])
            ->assertSessionHasErrors(['paid_amount', 'declaration']);
        $this->assertSame(ExamApplication::REJECTED, $this->application->fresh()->status);
    }

    public function test_only_rejected_applications_can_be_resubmitted(): void
    {
        $this->get(route('applicant.applications.resubmit.form', $this->application))
            ->assertRedirect(route('applicant.applications.show', $this->application))->assertSessionHas('error');
        $this->put(route('applicant.applications.resubmit', $this->application), ['paid_amount' => '2500', 'declaration' => '1'])
            ->assertSessionHas('error');
        $this->assertSame(1, $this->application->statusLogs()->count());
    }

    public function test_cannot_resubmit_after_the_application_period(): void
    {
        $this->reject();
        Carbon::setTestNow(Carbon::parse('2026-11-25 12:00', config('app.business_timezone')));

        $this->assertFalse($this->application->fresh()->canResubmit());
        $this->get(route('applicant.applications.show', $this->application))
            ->assertSee(__('The application period has closed. Contact the council to reopen your application.'))
            ->assertDontSee(route('applicant.applications.resubmit.form', $this->application), false);
        $this->put(route('applicant.applications.resubmit', $this->application), ['paid_amount' => '2500', 'declaration' => '1'])
            ->assertSessionHas('error');
        $this->assertSame(ExamApplication::REJECTED, $this->application->fresh()->status);
    }

    public function test_others_cannot_resubmit(): void
    {
        $this->reject();
        $this->actingAs($this->userWithRole(RoleEnum::APPLICANT));

        $this->get(route('applicant.applications.resubmit.form', $this->application))->assertNotFound();
        $this->put(route('applicant.applications.resubmit', $this->application), ['paid_amount' => '2500', 'declaration' => '1'])
            ->assertForbidden();
        $this->assertSame(ExamApplication::REJECTED, $this->application->fresh()->status);
    }
}
