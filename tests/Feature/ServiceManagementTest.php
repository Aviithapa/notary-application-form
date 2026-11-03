<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithRole(string $role): User
    {
        $user = User::create(['email' => $role . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', $role)->value('id'));

        return $user;
    }

    private function serviceData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Notary licence',
            'name_np' => 'नोटरी इजाजतपत्र',
            'purpose' => 'new_license',
            'icon' => 'bi-award',
            'is_fee_based' => '1',
            'fee_amount' => '5000',
            'requires_exam' => '1',
            'requires_application_form' => '1',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_admin_creates_service_and_sees_it_as_card_and_in_list(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::ADMIN));

        $this->post(route('services.store'), $this->serviceData())
            ->assertSessionHasNoErrors()->assertRedirect(route('services.index'));

        $service = Service::where('name', 'Notary licence')->firstOrFail();
        $this->assertTrue($service->is_fee_based);
        $this->assertSame('5000.00', $service->fee_amount);
        $this->assertTrue($service->requires_exam);

        $this->get(route('services.index'))->assertOk()
            ->assertSee('mb-0 service-card', false)->assertSee('Notary licence')->assertSee('5,000.00')
            ->assertSee(route('exams.create', ['service' => $service->id]), false);
        $this->get(route('services.index', ['view' => 'list']))->assertOk()
            ->assertDontSee('mb-0 service-card', false)->assertSee('Notary licence');
        $this->get(route('services.create'))->assertOk();
        $this->get(route('services.edit', $service))->assertOk();
    }

    public function test_fee_is_required_only_for_fee_based_services(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::ADMIN));

        $this->post(route('services.store'), $this->serviceData(['fee_amount' => '']))
            ->assertSessionHasErrors('fee_amount');

        // Unticked boxes are not submitted; a leftover fee is dropped
        $data = $this->serviceData(['name' => 'Free service', 'fee_amount' => '100']);
        unset($data['is_fee_based'], $data['requires_exam']);
        $this->post(route('services.store'), $data)->assertSessionHasNoErrors();

        $service = Service::where('name', 'Free service')->firstOrFail();
        $this->assertFalse($service->is_fee_based);
        $this->assertNull($service->fee_amount);
        $this->assertFalse($service->requires_exam);
    }

    public function test_exams_belong_to_services_that_require_them(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::ADMIN));
        $withExam = Service::create($this->serviceData(['requires_exam' => true]));
        $withoutExam = Service::create($this->serviceData(['name' => 'Renewal', 'requires_exam' => false]));

        $this->get(route('exams.create', ['service' => $withExam->id]))->assertOk();

        $this->post(route('exams.store'), [
            'service_id' => $withoutExam->id, 'title' => 'X', 'status' => 'draft',
        ])->assertSessionHasErrors('service_id');

        $this->post(route('exams.store'), [
            'service_id' => $withExam->id, 'title' => 'X', 'status' => 'draft', 'full_marks' => 100, 'pass_marks' => 120,
        ])->assertSessionHasErrors('pass_marks');

        // Application start and end are required, and the dates must be in order
        $this->post(route('exams.store'), [
            'service_id' => $withExam->id, 'title' => 'X', 'status' => 'draft',
        ])->assertSessionHasErrors(['application_start_bs', 'application_end_bs']);
        $this->post(route('exams.store'), [
            'service_id' => $withExam->id, 'title' => 'X', 'status' => 'draft',
            'application_start_ad' => '2026-11-10', 'application_end_ad' => '2026-11-05',
            'double_fee_end_ad' => '2026-11-04', 'exam_date_ad' => '2026-11-01',
        ])->assertSessionHasErrors(['application_end_bs', 'double_fee_end_bs']);

        $this->post(route('exams.store'), [
            'service_id' => $withExam->id, 'title' => 'Notary exam 2083', 'status' => 'published',
            'application_start_bs' => '2083-07-15', 'application_end_bs' => '2083-08-15', 'double_fee_end_bs' => '2083-08-22',
            'exam_date_ad' => '2026-12-20', 'start_time' => '10:00', 'duration_minutes' => 180,
            'full_marks' => 100, 'pass_marks' => 40,
        ])->assertSessionHasNoErrors()->assertRedirect(route('exams.index'));

        $exam = Exam::where('title', 'Notary exam 2083')->firstOrFail();
        $this->assertSame('2083-09-05', $exam->exam_date_bs);
        $this->assertSame('2026-11-01', $exam->application_start_ad);
        $this->assertNotNull($exam->double_fee_end_ad);

        $this->get(route('exams.index', ['service' => $withExam->id]))->assertOk()->assertSee('Notary exam 2083');
        $this->get(route('exams.edit', $exam))->assertOk()->assertSee('value="10:00"', false);

        // A service with exams cannot be deleted until its exams are gone
        $this->delete(route('services.destroy', $withExam))->assertSessionHas('error');
        $this->assertModelExists($withExam);
        $this->delete(route('exams.destroy', $exam));
        $this->delete(route('services.destroy', $withExam))->assertSessionHas('success');
        $this->assertModelMissing($withExam);
    }

    public function test_applicants_cannot_manage_services(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::APPLICANT));

        $this->get(route('services.index'))->assertRedirect('/dashboard');
        $this->post(route('services.store'), $this->serviceData())->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('services', ['name' => 'Notary licence']);
    }

    public function test_dashboard_has_language_switch(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::ADMIN));

        $this->get(route('dashboard'))->assertOk()
            ->assertSee(route('lang.switch', 'np'), false)
            ->assertSee(route('lang.switch', 'en'), false);

        $this->from(route('services.index'))->get(route('lang.switch', 'np'))->assertRedirect(route('services.index'));
        $this->get(route('services.index'))->assertSee('सेवाहरू');
    }
}
