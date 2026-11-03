<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;

/**
 * Applicants see exams that are taking applications and apply to them. The fee is fixed at
 * the moment of applying: the service fee, or twice that in the double-fee period.
 */
class ExamApplicationController extends Controller
{
    public function index()
    {
        $visible = fn ($query) => $query->with('service')->whereHas('service', fn ($s) => $s->where('is_active', true));

        return view('admin.pages.applicant.exams', [
            'open' => $visible(Exam::acceptingApplications())->orderBy('application_end_ad')->get(),
            'upcoming' => $visible(Exam::where('status', 'published')->where('application_start_ad', '>', Exam::today()))
                ->orderBy('application_start_ad')->get(),
            'appliedExamIds' => $this->user()->examApplications()->pluck('exam_id')->all(),
            'formComplete' => $this->formComplete($this->user()),
        ]);
    }

    public function apply(Exam $exam)
    {
        $user = $this->user();
        $exam->load('service');

        if (! $exam->service->is_active || ! $exam->isAcceptingApplications()) {
            return back()->with('error', __('Applications for this exam are not open.'));
        }

        if ($exam->service->requires_application_form && ! $this->formComplete($user)) {
            return redirect()->route('applicant.personal')
                ->with('error', __('Complete your personal details and qualifications before applying.'));
        }

        try {
            $user->examApplications()->create([
                'exam_id' => $exam->id,
                'fee_amount' => $exam->currentFee(),
                'is_double_fee' => $exam->phase() === Exam::PHASE_DOUBLE_FEE,
            ]);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('applicant.applications')->with('error', __('You have already applied for this exam.'));
        }

        return redirect()->route('applicant.applications')->with('success', __('Your application has been submitted.'));
    }

    public function applications()
    {
        return view('admin.pages.applicant.applications', [
            'applications' => $this->user()->examApplications()->with('exam.service')->latest('id')->get(),
        ]);
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    /** The application form counts as complete once personal details and qualifications are saved. */
    private function formComplete(User $user): bool
    {
        $progress = $user->applicationProgress();

        return $progress['personal'] && $progress['qualifications'];
    }
}
