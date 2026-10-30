<?php

namespace App\Http\Controllers\Admin;

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
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    protected $userRepository, $postRepository, $settingRepository;

    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        SettingRepository $settingRepository

    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->settingRepository = $settingRepository;
    }


    public function index()
    {
        $role = Auth::user()->mainRole() ? Auth::user()->mainRole()->name : 'default';
        $registrar = $this->postRepository->all()->where('excerpt','registrar')->first();
        $chairman = $this->postRepository->all()->where('excerpt','chairman')->first();
        $settings = $this->settingRepository->all();
        switch ($role) {
            case 'admin':
            case 'super_admin':
                $postCounts = Post::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
                $stats = [
                    ['label' => 'News & Notices', 'value' => News::count(), 'icon' => 'bi-newspaper', 'tone' => 'primary', 'url' => url('cms/news')],
                    ['label' => 'Legal Documents', 'value' => $postCounts['legal-documents'] ?? 0, 'icon' => 'bi-file-earmark-pdf', 'tone' => 'danger', 'url' => url('cms/legal-documents')],
                    ['label' => 'Board Members', 'value' => $postCounts['bod'] ?? 0, 'icon' => 'bi-people', 'tone' => 'info', 'url' => url('cms/bod')],
                    ['label' => 'Staff', 'value' => $postCounts['team'] ?? 0, 'icon' => 'bi-person-badge', 'tone' => 'success', 'url' => url('cms/staff')],
                    ['label' => 'Messages', 'value' => Contact::count(), 'icon' => 'bi-envelope', 'tone' => 'warning', 'url' => null],
                    ['label' => 'Subscribers', 'value' => NewsletterSubscriber::count(), 'icon' => 'bi-bell', 'tone' => 'secondary', 'url' => null],
                ];
                $directory = [
                    ['label' => 'Notary Publics', 'value' => DummyDirectory::notaries()->count(), 'url' => url('notary-public-list')],
                    ['label' => 'Translators', 'value' => DummyDirectory::translators()->count(), 'url' => url('translators')],
                    ['label' => 'District Changes', 'value' => DummyDirectory::districtChanges()->count(), 'url' => url('district-change-notary-public')],
                    ['label' => 'Cancelled', 'value' => DummyDirectory::cancelledTranslators()->count(), 'url' => url('cancelled-translators')],
                ];
                $latestNews = News::latest()->take(5)->get();
                $latestMessages = Contact::latest()->take(5)->get();
                $inquiryCount = Inquiry::count();
                return view('admin.dashboard.admin', compact(
                    'registrar', 'chairman', 'settings', 'stats', 'directory', 'latestNews', 'latestMessages', 'inquiryCount'
                ));
            default:
                return $this->view('dashboard.default');
                break;
        }
    }
}
