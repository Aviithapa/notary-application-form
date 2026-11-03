<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\ExamApplicationRequest;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Applicants see exams that are taking applications and apply through a form with payment proof,
 * signature and thumbprints. The fee is fixed at the moment of applying: the service fee, or twice
 * that in the double-fee period.
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

    /** The application form: payment voucher, amount paid, signature and thumbprints. */
    public function create(Exam $exam)
    {
        $user = $this->user();
        $exam->load('service');
        if ($redirect = $this->cannotApply($exam, $user)) {
            return $redirect;
        }

        return view('admin.pages.applicant.exam-apply', [
            'exam' => $exam,
            'user' => $user->loadApplicationForm(),
            'fee' => $exam->currentFee(),
            'doubleFee' => $exam->phase() === Exam::PHASE_DOUBLE_FEE,
        ]);
    }

    public function apply(ExamApplicationRequest $request, Exam $exam)
    {
        $user = $this->user();
        $exam->load('service');
        if ($redirect = $this->cannotApply($exam, $user)) {
            return $redirect;
        }

        try {
            DB::transaction(function () use ($request, $exam, $user) {
                $fee = $exam->currentFee();
                $application = $user->examApplications()->create([
                    'exam_id' => $exam->id,
                    'fee_amount' => $fee,
                    'is_double_fee' => $exam->phase() === Exam::PHASE_DOUBLE_FEE,
                    'paid_amount' => $fee === null ? null : $request->validated('paid_amount'),
                ]);

                $collections = array_keys(ExamApplication::IDENTITY_MARKS);
                if ($fee !== null) {
                    $collections[] = ExamApplication::VOUCHER;
                }
                foreach ($collections as $collection) {
                    $application->addMediaFromRequest($collection)->toMediaCollection($collection);
                }
            });
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('applicant.applications')->with('error', __('You have already applied for this exam.'));
        }

        return redirect()->route('applicant.applications')->with('success', __('Your application has been submitted.'));
    }

    /** The application form again, for fixing and resubmitting a rejected application. */
    public function editResubmit(ExamApplication $application)
    {
        $user = $this->user();
        abort_unless($application->user_id === $user->id, 404);
        $application->load(['exam.service', 'media']);
        if ($redirect = $this->cannotResubmit($application, $user)) {
            return $redirect;
        }

        return view('admin.pages.applicant.exam-apply', [
            'exam' => $application->exam,
            'user' => $user->loadApplicationForm(),
            'fee' => $application->fee_amount,
            'doubleFee' => $application->is_double_fee,
            'application' => $application,
        ]);
    }

    public function resubmit(ExamApplicationRequest $request, ExamApplication $application)
    {
        $user = $this->user();
        $application->load(['exam.service', 'media']);
        if ($redirect = $this->cannotResubmit($application, $user)) {
            return $redirect;
        }

        DB::transaction(function () use ($request, $application, $user) {
            if ($application->fee_amount !== null) {
                $application->update(['paid_amount' => $request->validated('paid_amount')]);
            }

            // Replace only the files the applicant chose again; the rest are kept
            foreach ([ExamApplication::VOUCHER, ...array_keys(ExamApplication::IDENTITY_MARKS)] as $collection) {
                if ($request->hasFile($collection)) {
                    $application->addMediaFromRequest($collection)->toMediaCollection($collection);
                }
            }

            $application->changeStatus(ExamApplication::PENDING, __('Resubmitted by the applicant.'), $user);
        });

        return redirect()->route('applicant.applications.show', $application)
            ->with('success', __('Your application has been resubmitted.'));
    }

    /** Redirect explaining why this application cannot be resubmitted now, or null if it can. */
    private function cannotResubmit(ExamApplication $application, User $user): ?RedirectResponse
    {
        $back = redirect()->route('applicant.applications.show', $application);

        if ($application->status !== ExamApplication::REJECTED) {
            return $back->with('error', __('Only rejected applications can be resubmitted.'));
        }
        if (! $application->canResubmit()) {
            return $back->with('error', __('The application period has closed. Contact the council to reopen your application.'));
        }
        if ($application->exam->service->requires_application_form && ! $this->formComplete($user)) {
            return redirect()->route('applicant.personal')
                ->with('error', __('Complete your personal details and qualifications before applying.'));
        }

        return null;
    }

    /** Redirect explaining why this user cannot apply to the exam now, or null if they can. */
    private function cannotApply(Exam $exam, User $user): ?RedirectResponse
    {
        if (! $exam->service->is_active || ! $exam->isAcceptingApplications()) {
            return redirect()->route('applicant.exams')->with('error', __('Applications for this exam are not open.'));
        }

        if ($user->examApplications()->where('exam_id', $exam->id)->exists()) {
            return redirect()->route('applicant.applications')->with('error', __('You have already applied for this exam.'));
        }

        if ($exam->service->requires_application_form && ! $this->formComplete($user)) {
            return redirect()->route('applicant.personal')
                ->with('error', __('Complete your personal details and qualifications before applying.'));
        }

        return null;
    }

    public function applications()
    {
        return view('admin.pages.applicant.applications', [
            'applications' => $this->user()->examApplications()->with('exam.service')->latest('id')->get(),
        ]);
    }

    public function show(ExamApplication $application)
    {
        abort_unless($application->user_id === $this->user()->id, 404);

        return view('admin.pages.applications.show', [
            'application' => $application->loadFullDetails()->load('statusLogs'),
            'backUrl' => route('applicant.applications'),
        ]);
    }

    public function admitCard(ExamApplication $application)
    {
        abort_unless($application->user_id === $this->user()->id && $application->hasAdmitCard(), 404);

        return view('admin.pages.applications.admit-card', ['application' => $application->loadFullDetails()]);
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
