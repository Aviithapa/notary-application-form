<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Inquiry;
use App\Models\News;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\TaskAssignment;
use App\Support\NotaryDirectory\DummyDirectory;
use App\Repositories\CMS\Post\PostRepository;
use App\Repositories\Setting\SettingRepository;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->mainRole() ? Auth::user()->mainRole()->name : 'default';
        switch ($role) {
            case 'admin':
            case 'super_admin':
            
                return view('admin.dashboard.admin');
            case RoleEnum::APPLICANT:
                return view('admin.dashboard.applicant', ['user' => Auth::user()]);
            default:
                return view('admin.dashboard.default');
        }
    }
}
