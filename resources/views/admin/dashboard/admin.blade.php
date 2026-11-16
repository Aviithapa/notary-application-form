@extends('admin.layout.app')

{{-- Admin dashboard: what needs attention today, open calls, status overview and recent activity. --}}
@php
    use App\Models\ExamApplication;
    use App\Support\NepaliDate;

    $tz = config('app.business_timezone');
    $now = now($tz);
    $greeting = $now->hour < 12 ? __('Good morning') : ($now->hour < 17 ? __('Good afternoon') : __('Good evening'));

    $statusCounts = $dashboard->statusCounts();
    $total = $statusCounts->sum();
    $pending = $statusCounts[ExamApplication::PENDING];
    $admitCards = collect(ExamApplication::ADMIT_CARD_STATUSES)->sum(fn ($status) => $statusCounts[$status]);
    $openCalls = $dashboard->openCalls();
    $pendingByService = $dashboard->pendingByService();
    $daily = $dashboard->dailyApplications();
    $dailyMax = max(1, $daily->max());
    $upcoming = $dashboard->upcomingExams();
    $recent = $dashboard->recentApplications();

    // Solid colours for the status bar, matching the status badges
    $statusColor = [
        ExamApplication::PENDING => '#f5a623',
        ExamApplication::APPROVED => '#39afd1',
        ExamApplication::READY_FOR_ADMIT_CARD => '#3e60d5',
        ExamApplication::ADMIT_CARD_GENERATED => '#313a46',
        ExamApplication::PASSED => '#47ad77',
        ExamApplication::FAILED => '#fa5c7c',
        ExamApplication::REJECTED => '#b02a37',
    ];
    $money = fn ($amount) => __('Rs.') . ' ' . number_format($amount, 0);
    $daysLeft = fn ($date) => (int) \Illuminate\Support\Carbon::parse(\App\Models\Exam::today())->diffInDays($date);
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Dashboard') }}</h4>
            </div>
        </div>
    </div>

    {{-- Welcome and quick actions --}}
    <div class="dash-welcome mb-3">
        <div>
            <div class="small opacity-75">{{ NepaliDate::adToBs($now->toDateString()) }} {{ __('B.S.') }} · {{ $now->format('l, j F Y') }}</div>
            <h3 class="mb-1 mt-1 text-white">{{ $greeting }}, {{ $user->username ?: strtok($user->email, '@') }}</h3>
            <div class="opacity-75">
                @if ($pending)
                    {{ trans_choice(':count application is waiting for review.|:count applications are waiting for review.', $pending) }}
                @else
                    {{ __('No applications are waiting for review.') }}
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('applications.index', ['status' => ExamApplication::PENDING]) }}" class="btn btn-light">
                <i class="bi bi-hourglass-split me-1"></i> {{ __('Review pending') }}
                @if ($pending) <span class="badge bg-warning text-dark ms-1">{{ $pending }}</span> @endif
            </a>
            <a href="{{ route('exams.create') }}" class="btn btn-outline-light"><i class="bi bi-calendar-plus me-1"></i> {{ __('Open applications') }}</a>
            <a href="{{ route('services.create') }}" class="btn btn-outline-light"><i class="bi bi-plus-lg me-1"></i> {{ __('Add service') }}</a>
        </div>
    </div>

    {{-- Key figures --}}
    <div class="row g-3 mb-3">
        @foreach ([
            ['label' => __('Pending review'), 'value' => $pending, 'icon' => 'bi-hourglass-split', 'tone' => 'warning', 'url' => route('applications.index', ['status' => ExamApplication::PENDING])],
            ['label' => __('Applications'), 'value' => $total, 'icon' => 'bi-inbox', 'tone' => 'primary', 'url' => route('applications.index')],
            ['label' => __('Open for applications'), 'value' => $openCalls->count(), 'icon' => 'bi-calendar-check', 'tone' => 'success', 'url' => route('exams.index')],
            ['label' => __('Applicants'), 'value' => $dashboard->applicantCount(), 'icon' => 'bi-people', 'tone' => 'info', 'url' => route('user.index'),
                'note' => trans_choice('+:count this week|+:count this week', $dashboard->newApplicantsThisWeek())],
            ['label' => __('Fees received'), 'value' => $money($dashboard->feesReceived()), 'icon' => 'bi-cash-coin', 'tone' => 'secondary', 'note' => __('excluding rejected')],
            ['label' => __('Admit cards issued'), 'value' => $admitCards, 'icon' => 'bi-person-badge', 'tone' => 'dark',
                'url' => route('applications.index', ['status' => ExamApplication::ADMIT_CARD_GENERATED])],
        ] as $tile)
            <div class="col-6 col-md-4 col-xxl-2">
                <a @isset($tile['url']) href="{{ $tile['url'] }}" @endisset class="card h-100 mb-0 dash-tile text-reset text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">{{ $tile['label'] }}</span>
                            <span class="dash-tile-icon bg-{{ $tile['tone'] }}-subtle text-{{ $tile['tone'] }}"><i class="bi {{ $tile['icon'] }}"></i></span>
                        </div>
                        <div class="fs-3 fw-semibold lh-1">{{ $tile['value'] }}</div>
                        @isset($tile['note']) <div class="text-muted small mt-1">{{ $tile['note'] }}</div> @endisset
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            {{-- Open application periods --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title m-0">{{ __('Open for applications') }}</h5>
                        <a href="{{ route('exams.index') }}" class="small">{{ __('All exams') }} →</a>
                    </div>
                    @if ($openCalls->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x fs-2 d-block mb-1"></i>
                            {{ __('No exams are taking applications right now.') }}
                            <div><a href="{{ route('exams.create') }}" class="small">{{ __('Open applications') }}</a></div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Exam') }}</th>
                                        <th>{{ __('Last date') }}</th>
                                        <th class="text-center">{{ __('Applications') }}</th>
                                        <th class="text-center">{{ __('Pending') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openCalls as $exam)
                                        @php $left = $daysLeft($exam->lastApplicationDate()); @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $exam->localized('title') }}</div>
                                                <div class="text-muted small">{{ $exam->service->localized('name') }} · @include('admin.pages.exams.partials.phase')</div>
                                            </td>
                                            <td class="text-nowrap small">
                                                {{ NepaliDate::adToBs($exam->lastApplicationDate()) }}
                                                <div class="{{ $left <= 3 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                    {{ $left === 0 ? __('Last day today') : trans_choice(':count day left|:count days left', $left) }}
                                                </div>
                                            </td>
                                            <td class="text-center fw-semibold">{{ $exam->applications_count }}</td>
                                            <td class="text-center">
                                                @if ($exam->pending_count)
                                                    <span class="badge bg-warning text-dark">{{ $exam->pending_count }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('exams.applications', $exam) }}" class="btn btn-sm btn-light">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent applications --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title m-0">{{ __('Recent applications') }}</h5>
                        <a href="{{ route('applications.index') }}" class="small">{{ __('All applications') }} →</a>
                    </div>
                    @if ($recent->isEmpty())
                        <div class="text-center text-muted py-4">{{ __('No applications yet.') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <tbody>
                                    @foreach ($recent as $application)
                                        <tr>
                                            <td style="min-width: 170px;">
                                                <div class="fw-semibold">{{ $application->user->applicantProfile?->localized('name') ?: $application->user->email }}</div>
                                                <div class="text-muted small text-nowrap">{{ $application->application_number }}</div>
                                            </td>
                                            <td class="small">
                                                {{ $application->exam->localized('title') }}
                                                <div class="text-muted">{{ $application->exam->service->localized('name') }}</div>
                                            </td>
                                            <td class="small text-muted text-nowrap">{{ $application->created_at->setTimezone($tz)->diffForHumans() }}</td>
                                            <td>@include('admin.pages.applications.partials.status')</td>
                                            <td class="text-end">
                                                <a href="{{ route('exams.applications.show', [$application->exam_id, $application]) }}" class="btn btn-sm btn-light text-nowrap" title="{{ __('View') }}">
                                                    <i class="bi bi-eye"></i><span class="d-none d-xxl-inline ms-1">{{ __('View') }}</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- Needs attention: pending per service --}}
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Needs attention') }}</h5>
                    @forelse ($pendingByService as $service)
                        <a href="{{ route('applications.index', ['service' => $service->id, 'status' => ExamApplication::PENDING]) }}"
                            class="d-flex align-items-center gap-2 py-2 border-bottom text-reset text-decoration-none dash-attention">
                            <span class="dash-tile-icon bg-primary-subtle text-primary"><i class="bi {{ $service->icon }}"></i></span>
                            <span class="flex-grow-1">{{ $service->localized('name') }}</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $service->pending_count }}</span>
                            <i class="bi bi-chevron-right text-muted small"></i>
                        </a>
                    @empty
                        <div class="text-muted small"><i class="bi bi-check2-circle text-success me-1"></i>{{ __('All caught up. Nothing is waiting for review.') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- Applications by status --}}
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Applications by status') }}</h5>
                    @if ($total === 0)
                        <div class="text-muted small">{{ __('No applications yet.') }}</div>
                    @else
                        <div class="progress mb-3 dash-status-bar" style="height: 12px;" role="img"
                            aria-label="{{ $statusCounts->filter()->map(fn ($n, $s) => __(ExamApplication::STATUSES[$s][0]) . ': ' . $n)->implode(', ') }}">
                            @foreach ($statusCounts->filter() as $status => $count)
                                <div class="progress-bar" style="width: {{ $count / $total * 100 }}%; background-color: {{ $statusColor[$status] }};"
                                    title="{{ __(ExamApplication::STATUSES[$status][0]) }}: {{ $count }}"></div>
                            @endforeach
                        </div>
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($statusCounts as $status => $count)
                                <li>
                                    <a href="{{ route('applications.index', ['status' => $status]) }}" class="d-flex align-items-center gap-2 py-1 text-reset text-decoration-none">
                                        <span class="dash-dot" style="background-color: {{ $statusColor[$status] }};"></span>
                                        <span class="flex-grow-1">{{ __(ExamApplication::STATUSES[$status][0]) }}</span>
                                        <span class="fw-semibold {{ $count ? '' : 'text-muted' }}">{{ $count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Last 14 days --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="card-title m-0">{{ __('New applications') }}</h5>
                        <span class="text-muted small">{{ __('Last 14 days') }} · {{ $daily->sum() }}</span>
                    </div>
                    <div class="dash-days" role="img" aria-label="{{ $daily->map(fn ($n, $d) => "$d: $n")->implode(', ') }}">
                        @foreach ($daily as $day => $count)
                            <div class="dash-day" title="{{ NepaliDate::adToBs($day) }} ({{ $day }}): {{ $count }}">
                                <div class="dash-day-bar {{ $loop->last ? 'is-today' : '' }}" style="height: {{ max(3, $count / $dailyMax * 100) }}%;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: .7rem;">
                        <span>{{ \Illuminate\Support\Carbon::parse($daily->keys()->first())->format('M j') }}</span>
                        <span>{{ __('Today') }}</span>
                    </div>
                </div>
            </div>

            {{-- Upcoming exams --}}
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Upcoming exams') }}</h5>
                    @forelse ($upcoming as $exam)
                        <div class="d-flex gap-3 py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                            <div class="dash-date text-center">
                                <div class="dash-date-day">{{ substr($exam->exam_date_bs, 8, 2) }}</div>
                                <div class="dash-date-month">{{ substr($exam->exam_date_bs, 0, 7) }}</div>
                            </div>
                            <div class="flex-grow-1 small">
                                <a href="{{ route('exams.applications', $exam) }}" class="fw-semibold text-reset">{{ $exam->localized('title') }}</a>
                                <div class="text-muted">
                                    {{ $exam->start_time ? substr($exam->start_time, 0, 5) . ' · ' : '' }}{{ $exam->localized('venue') ?: __('Venue not set') }}
                                </div>
                                <div class="text-muted">
                                    <i class="bi bi-person-badge"></i> {{ __(':issued admit cards issued', ['issued' => $exam->admit_cards_count]) }}
                                    @if ($exam->approved_count) · <span class="text-warning">{{ __(':count still to issue', ['count' => $exam->approved_count]) }}</span> @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">{{ __('No upcoming exams scheduled.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .dash-welcome {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 1.5rem 1.75rem; border-radius: 14px; color: #fff;
            background: linear-gradient(120deg, #003893 0%, #00296b 65%, #8a1538 140%);
        }
        .dash-tile { transition: box-shadow .15s ease, transform .15s ease; }
        a.dash-tile[href]:hover { box-shadow: 0 .5rem 1.25rem rgba(0, 56, 147, .12); transform: translateY(-2px); }
        .dash-tile-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; font-size: 1.05rem; flex: none; }
        .dash-attention:last-child { border-bottom: 0 !important; }
        .dash-attention:hover { background: #f6f8fc; }
        .dash-dot { width: 10px; height: 10px; border-radius: 50%; flex: none; }
        .dash-days { display: flex; align-items: flex-end; gap: 4px; height: 90px; margin-bottom: .35rem; }
        .dash-day { flex: 1; height: 100%; display: flex; align-items: flex-end; }
        .dash-day-bar { width: 100%; border-radius: 3px 3px 0 0; background: rgba(0, 56, 147, .35); }
        .dash-day-bar.is-today { background: #003893; }
        .dash-date { width: 52px; flex: none; border-radius: 10px; background: rgba(0, 56, 147, .08); color: #003893; padding: .35rem 0; }
        .dash-date-day { font-size: 1.25rem; font-weight: 700; line-height: 1; }
        .dash-date-month { font-size: .7rem; }
    </style>
@endpush
