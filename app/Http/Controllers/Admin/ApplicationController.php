<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\Service;
use Illuminate\Http\Request;

/** Applications across exams, optionally narrowed to one service, one exam and one status. */
class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $service = $request->filled('service') ? Service::findOrFail($request->integer('service')) : null;
        $examId = $request->integer('exam') ?: null;
        // A service without an exam has a shorter workflow, so fewer status tabs
        $statuses = ExamApplication::statusesFor($service);
        $status = in_array($request->query('status'), $statuses, true) ? $request->query('status') : null;

        // Service and exam filters, shared by the list and the status counts
        $filtered = fn () => ExamApplication::query()
            ->when($service, fn ($query) => $query->whereHas('exam', fn ($exam) => $exam->where('service_id', $service->id)))
            ->when($examId, fn ($query) => $query->where('exam_id', $examId));

        return view('admin.pages.applications.index', [
            'service' => $service,
            'examId' => $examId,
            'status' => $status,
            'statuses' => $statuses,
            'exams' => Exam::when($service, fn ($query) => $query->where('service_id', $service->id))
                ->with('service')->orderByDesc('application_start_ad')->get(),
            'applications' => $filtered()
                ->with(['user.applicantProfile', 'media', 'exam.service'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest('id')->paginate(50)->withQueryString(),
            'statusCounts' => $filtered()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
