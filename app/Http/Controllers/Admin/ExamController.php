<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamRequest;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\Service;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $serviceId = $request->integer('service') ?: null;

        return view('admin.pages.exams.index', [
            'exams' => Exam::with('service')->withCount('applications')
                ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
                ->orderByDesc('application_start_ad')
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'services' => $this->examServices(),
            'serviceId' => $serviceId,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.pages.exams.form', [
            'exam' => new Exam(['service_id' => $request->integer('service') ?: null, 'status' => 'draft']),
            'services' => $this->examServices(),
        ]);
    }

    public function store(ExamRequest $request)
    {
        Exam::create($request->examData());

        return redirect()->route('exams.index')->with('success', __('Exam created.'));
    }

    public function edit(Exam $exam)
    {
        return view('admin.pages.exams.form', [
            'exam' => $exam,
            'services' => $this->examServices(),
        ]);
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $exam->update($request->examData());

        return redirect()->route('exams.index')->with('success', __('Exam updated.'));
    }

    public function applications(Request $request, Exam $exam)
    {
        $status = array_key_exists($request->query('status'), ExamApplication::STATUSES) ? $request->query('status') : null;

        return view('admin.pages.exams.applications', [
            'exam' => $exam->load('service'),
            'applications' => $exam->applications()->with(['user.applicantProfile', 'media'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest('id')->paginate(50)->withQueryString(),
            'statusCounts' => $exam->applications()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'status' => $status,
        ]);
    }

    public function showApplication(Exam $exam, ExamApplication $application)
    {
        abort_unless($application->exam_id === $exam->id, 404);

        return view('admin.pages.applications.show', [
            'application' => $application->loadFullDetails()->load('statusLogs.changedBy'),
            'backUrl' => route('exams.applications', $exam),
            'admin' => true,
        ]);
    }

    public function destroy(Exam $exam)
    {
        if ($exam->applications()->exists()) {
            return back()->with('error', __('This exam has applications and cannot be deleted. Mark it cancelled instead.'));
        }

        $exam->delete();

        return back()->with('success', __('Exam deleted.'));
    }

    /** Services an exam can belong to. */
    private function examServices()
    {
        return Service::where('requires_exam', true)->ordered()->get();
    }
}
