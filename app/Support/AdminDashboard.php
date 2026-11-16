<?php

namespace App\Support;

use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\ExamApplication;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Figures for the admin dashboard. Each method is one panel; dates are in Nepal time. */
class AdminDashboard
{
    private string $tz;

    public function __construct()
    {
        $this->tz = config('app.business_timezone');
    }

    /** @return Collection<string, int> status => number of applications */
    public function statusCounts(): Collection
    {
        $counts = ExamApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(array_keys(ExamApplication::STATUSES))->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)]);
    }

    public function applicantCount(): int
    {
        return User::whereHas('roles', fn ($query) => $query->where('name', RoleEnum::APPLICANT))->count();
    }

    public function newApplicantsThisWeek(): int
    {
        return User::whereHas('roles', fn ($query) => $query->where('name', RoleEnum::APPLICANT))
            ->where('created_at', '>=', now($this->tz)->subDays(7))
            ->count();
    }

    /** Amount paid on applications that were not rejected. */
    public function feesReceived(): float
    {
        return (float) ExamApplication::where('status', '!=', ExamApplication::REJECTED)->sum('paid_amount');
    }

    /** Services with applications waiting for review, most first. */
    public function pendingByService(): Collection
    {
        return Service::withCount(['applications as pending_count' => fn ($query) => $query->where('exam_applications.status', ExamApplication::PENDING)])
            ->get()
            ->where('pending_count', '>', 0)
            ->sortByDesc('pending_count')
            ->values();
    }

    /** Calls taking applications today, closing soonest first. */
    public function openCalls(): Collection
    {
        return Exam::acceptingApplications()
            ->with('service')
            ->withCount([
                'applications',
                'applications as pending_count' => fn ($query) => $query->where('status', ExamApplication::PENDING),
            ])
            ->orderByRaw('COALESCE(double_fee_end_ad, application_end_ad)')
            ->get();
    }

    /** Published exams with an exam date from today on, soonest first. */
    public function upcomingExams(int $limit = 5): Collection
    {
        return Exam::where('status', 'published')
            ->where('exam_date_ad', '>=', Exam::today())
            ->with('service')
            ->withCount([
                'applications as admit_cards_count' => fn ($query) => $query->whereIn('status', ExamApplication::ADMIT_CARD_STATUSES),
                'applications as approved_count' => fn ($query) => $query->whereIn('status', [ExamApplication::APPROVED, ExamApplication::READY_FOR_ADMIT_CARD]),
            ])
            ->orderBy('exam_date_ad')
            ->limit($limit)
            ->get();
    }

    public function recentApplications(int $limit = 8): Collection
    {
        return ExamApplication::with(['user.applicantProfile', 'exam.service'])->latest('id')->limit($limit)->get();
    }

    /**
     * New applications per day for the last $days days (oldest first), counted by Nepal date.
     *
     * @return Collection<string, int> Y-m-d => count
     */
    public function dailyApplications(int $days = 14): Collection
    {
        $today = Carbon::now($this->tz)->startOfDay();
        $from = $today->copy()->subDays($days - 1);

        // Nepal has a fixed UTC offset, so shifting the stored UTC time gives the local date
        $offset = $today->utcOffset();
        $counts = ExamApplication::where('created_at', '>=', $from->copy()->utc())
            ->selectRaw('DATE(DATE_ADD(created_at, INTERVAL ? MINUTE)) as day, count(*) as total', [$offset])
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))->mapWithKeys(function ($i) use ($from, $counts) {
            $day = $from->copy()->addDays($i)->toDateString();

            return [$day => (int) ($counts[$day] ?? 0)];
        });
    }
}
