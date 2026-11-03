<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithRole(string $role): User
    {
        $user = User::create(['email' => $role . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);
        $user->roles()->attach(Role::where('name', $role)->value('id'));

        return $user;
    }

    public function test_admin_pages_render_with_the_admin_sidebar(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::ADMIN));

        foreach (['dashboard', 'user.index', 'change.password'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee(__('User Management'))
                ->assertSee(route('change.password'), false);
        }
    }

    public function test_applicants_get_the_applicant_sidebar(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::APPLICANT))
            ->get(route('dashboard'))->assertOk()
            ->assertSee(route('applicant.personal'), false)
            ->assertDontSee(__('User Management'));
    }
}
