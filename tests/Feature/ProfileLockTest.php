<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\ExamApplication;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfileLockTest extends TestCase
{
    use DatabaseTransactions;

    private User $applicant;
    private User $admin;
    private ExamApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicant = $this->userWithRole(RoleEnum::APPLICANT);
        $this->applicant->applicantProfile()->create(['name' => 'Original Name']);
        $this->applicant->qualifications()->create(['university_name' => 'TU', 'level' => 'LLB']);
        $this->admin = $this->userWithRole(RoleEnum::ADMIN);

        $exam = Service::create([
            'name' => 'Licence', 'name_np' => 'इजाजतपत्र', 'purpose' => 'new_license', 'icon' => 'bi-award',
            'requires_exam' => true, 'is_active' => true,
        ])->exams()->create(['title' => 'Notary exam', 'status' => 'published']);
        $this->application = $this->applicant->examApplications()->create(['exam_id' => $exam->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::create(['email' => $role . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', $role)->value('id'));

        return $user;
    }

    public function test_details_can_be_edited_while_pending_or_rejected(): void
    {
        $this->actingAs($this->applicant);

        $this->assertFalse($this->applicant->profileLocked());
        $this->post(route('applicant.personal.save'), ['name' => 'Edited while pending'])->assertSessionHasNoErrors();
        $this->assertSame('Edited while pending', $this->applicant->applicantProfile->fresh()->name);

        $this->application->changeStatus(ExamApplication::REJECTED, 'Wrong photo', $this->admin);
        $this->assertFalse($this->applicant->profileLocked());
        $this->post(route('applicant.personal.save'), ['name' => 'Fixed after rejection'])->assertSessionHasNoErrors();
        $this->assertSame('Fixed after rejection', $this->applicant->applicantProfile->fresh()->name);

        $this->get(route('applicant.personal'))->assertOk()->assertDontSee(__('Your details are locked.'));
    }

    public function test_details_are_locked_once_an_application_is_approved(): void
    {
        $this->application->changeStatus(ExamApplication::APPROVED, null, $this->admin);
        $this->actingAs($this->applicant);
        $this->assertTrue($this->applicant->profileLocked());

        // Pages are read-only
        foreach (['applicant.personal', 'applicant.qualifications', 'applicant.trainings'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee(__('Your details are locked.'))
                ->assertSee('<fieldset disabled', false)
                ->assertDontSee(__('Save and continue'));
        }

        // And the server refuses every save
        $this->from(route('applicant.personal'))
            ->post(route('applicant.personal.save'), ['name' => 'Sneaky edit'])
            ->assertRedirect(route('applicant.personal'))->assertSessionHas('error');
        $this->post(route('applicant.qualifications.save'), ['qualifications' => []])->assertSessionHas('error');
        $this->post(route('applicant.trainings.save'), ['trainings' => [['training_name' => 'New']]])->assertSessionHas('error');

        $this->assertSame('Original Name', $this->applicant->applicantProfile->fresh()->name);
        $this->assertSame(1, $this->applicant->qualifications()->count());
        $this->assertSame(0, $this->applicant->trainings()->count());
    }

    public function test_lock_stays_after_results(): void
    {
        foreach ([ExamApplication::APPROVED, ExamApplication::READY_FOR_ADMIT_CARD, ExamApplication::ADMIT_CARD_GENERATED, ExamApplication::PASSED] as $status) {
            $this->application->changeStatus($status, null, $this->admin);
        }

        $this->actingAs($this->applicant)->post(route('applicant.personal.save'), ['name' => 'Late edit'])->assertSessionHas('error');
        $this->assertSame('Original Name', $this->applicant->applicantProfile->fresh()->name);
    }
}
