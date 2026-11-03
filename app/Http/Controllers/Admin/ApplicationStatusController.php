<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamApplication;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin status changes on exam applications, one at a time or in bulk, and the admit card. */
class ApplicationStatusController extends Controller
{
    public function update(Request $request, Exam $exam, ExamApplication $application)
    {
        abort_unless($application->exam_id === $exam->id, 404);
        $data = $this->validated($request);

        try {
            $application->changeStatus($data['status'], $data['remarks'] ?? null, $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Status changed to :status.', ['status' => $application->statusLabel()]));
    }

    public function bulk(Request $request, Exam $exam)
    {
        $data = $this->validated($request) + $request->validate([
            'applications' => ['required', 'array', 'max:500'],
            'applications.*' => ['integer'],
        ]);

        $changed = 0;
        $skipped = 0;
        $exam->applications()->whereIn('id', $data['applications'])->orderBy('id')->get()
            ->each(function (ExamApplication $application) use ($data, $request, &$changed, &$skipped) {
                try {
                    $application->changeStatus($data['status'], $data['remarks'] ?? null, $request->user());
                    $changed++;
                } catch (DomainException) {
                    $skipped++;
                }
            });

        $status = __(ExamApplication::STATUSES[$data['status']][0]);
        $message = trans_choice(':count application moved to :status.|:count applications moved to :status.', $changed, ['status' => $status]);
        if ($skipped) {
            $message .= ' ' . trans_choice(':count skipped because it cannot move to that status.|:count skipped because they cannot move to that status.', $skipped);
        }

        return back()->with($changed ? 'success' : 'error', $message);
    }

    public function admitCard(Exam $exam, ExamApplication $application)
    {
        abort_unless($application->exam_id === $exam->id && $application->hasAdmitCard(), 404);

        return view('admin.pages.applications.admit-card', ['application' => $application->loadFullDetails()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'status' => ['required', Rule::in(array_keys(ExamApplication::STATUSES))],
            'remarks' => ['nullable', 'string', 'max:1000', 'required_if:status,' . ExamApplication::REJECTED],
        ], [
            'remarks.required_if' => __('Give a reason for rejecting the application.'),
        ]);
    }
}
