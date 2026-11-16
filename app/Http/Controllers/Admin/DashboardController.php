<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\User;
use App\Support\AdminDashboard;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return match ($user->mainRole()?->name) {
            RoleEnum::ADMIN, RoleEnum::SUPER_ADMIN => view('admin.dashboard.admin', ['dashboard' => new AdminDashboard, 'user' => $user]),
            RoleEnum::APPLICANT => view('admin.dashboard.applicant', [
                'user' => $user,
                'progress' => $user->applicationProgress(),
                'openExams' => Exam::acceptingApplications()->whereHas('service', fn ($s) => $s->where('is_active', true))->count(),
                'applicationsCount' => $user->examApplications()->count(),
            ]),
            default => view('admin.dashboard.default'),
        };
    }
}
