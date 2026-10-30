@extends('admin.layout.app')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $quickActions = [
            ['label' => 'Add News', 'icon' => 'bi-plus-circle', 'url' => url('cms/news/create')],
            ['label' => 'Upload Legal Docs', 'icon' => 'bi-cloud-upload', 'url' => url('cms/legal-documents')],
            ['label' => 'Board Members', 'icon' => 'bi-people', 'url' => url('cms/bod')],
            ['label' => 'Staff', 'icon' => 'bi-person-badge', 'url' => url('cms/staff')],
            ['label' => 'Banners', 'icon' => 'bi-images', 'url' => url('cms/banner')],
            ['label' => 'Site Settings', 'icon' => 'bi-gear', 'url' => route('dashboard.site-settings.index')],
        ];
    @endphp

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ getSiteSetting('site_title') }}</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Dashboard</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- welcome banner -->
    <div class="dash-welcome mb-4">
        <div class="dash-welcome-text">
            <span class="dash-welcome-date"><i class="bi-calendar3"></i> {{ now()->format('l, d F Y') }}</span>
            <h2>{{ $greeting }}, {{ Auth::user()->name ?? 'Admin' }} 👋</h2>
            <p>Here's what's happening on the {{ getSiteSetting('site_title') ?: 'Notary Public Council' }} website today.</p>
        </div>
        <div class="dash-welcome-actions">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-light">
                <i class="bi-box-arrow-up-right"></i> View Website
            </a>
            <a href="{{ url('cms/news/create') }}" class="btn dash-btn-accent">
                <i class="bi-plus-lg"></i> New Post
            </a>
        </div>
    </div>

    <!-- stats -->
    <div class="row g-3 mb-4">
        @foreach ($stats as $stat)
            <div class="col-xl-2 col-md-4 col-6">
                <a @if ($stat['url']) href="{{ $stat['url'] }}" @endif class="dash-stat dash-tone-{{ $stat['tone'] }}">
                    <span class="dash-stat-icon"><i class="{{ $stat['icon'] }}"></i></span>
                    <span class="dash-stat-value">{{ number_format($stat['value']) }}</span>
                    <span class="dash-stat-label">{{ $stat['label'] }}</span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <!-- latest news -->
        <div class="col-xl-8">
            <div class="card dash-card h-100">
                <div class="dash-card-header">
                    <h5><i class="bi-newspaper"></i> Latest News & Notices</h5>
                    <a href="{{ url('cms/news') }}" class="dash-link">View all <i class="bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    @forelse ($latestNews as $item)
                        <div class="dash-news-item">
                            <div class="dash-news-date">
                                <strong>{{ $item->created_at?->format('d') }}</strong>
                                <span>{{ $item->created_at?->format('M') }}</span>
                            </div>
                            <div class="dash-news-body">
                                <h6>{{ $item->title }}</h6>
                                <div class="dash-news-meta">
                                    <span class="badge {{ $item->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($item->status ?? 'pending') }}
                                    </span>
                                    @if ($item->is_popup)
                                        <span class="badge bg-info"><i class="bi-window"></i> Popup</span>
                                    @endif
                                    <span class="text-muted">{{ $item->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="dash-news-actions">
                                <a href="{{ url('news-details/' . $item->id) }}" target="_blank" class="btn btn-sm btn-light" title="View on site">
                                    <i class="bi-eye"></i>
                                </a>
                                <a href="{{ url('cms/news/' . $item->id . '/edit') }}" class="btn btn-sm btn-light" title="Edit">
                                    <i class="bi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty">
                            <i class="bi-newspaper"></i>
                            <p>No news yet.</p>
                            <a href="{{ url('cms/news/create') }}" class="btn btn-sm btn-primary">Add the first one</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- quick actions -->
        <div class="col-xl-4">
            <div class="card dash-card h-100">
                <div class="dash-card-header">
                    <h5><i class="bi-lightning-charge"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="dash-actions">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['url'] }}" class="dash-action">
                                <i class="{{ $action['icon'] }}"></i>
                                <span>{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- latest messages -->
        <div class="col-xl-8">
            <div class="card dash-card h-100">
                <div class="dash-card-header">
                    <h5><i class="bi-envelope"></i> Recent Contact Messages</h5>
                    @if ($inquiryCount)
                        <a href="{{ route('inquiry.index') }}" class="dash-link">{{ $inquiryCount }} inquiries <i class="bi-arrow-right"></i></a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @forelse ($latestMessages as $message)
                        <div class="dash-message">
                            <span class="dash-message-avatar">{{ mb_strtoupper(mb_substr($message->name, 0, 1)) }}</span>
                            <div class="dash-message-body">
                                <div class="dash-message-head">
                                    <strong>{{ $message->name }}</strong>
                                    <span class="text-muted">{{ $message->created_at?->diffForHumans() }}</span>
                                </div>
                                <p>{{ \Illuminate\Support\Str::limit($message->message, 140) }}</p>
                                <div class="dash-message-contact">
                                    <a href="mailto:{{ $message->email }}"><i class="bi-envelope"></i> {{ $message->email }}</a>
                                    @if ($message->phone)
                                        <a href="tel:{{ $message->phone }}"><i class="bi-telephone"></i> {{ $message->phone }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty">
                            <i class="bi-inbox"></i>
                            <p>No messages from the contact form yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- leadership + directory -->
        <div class="col-xl-4">
            <div class="card dash-card mb-3">
                <div class="dash-card-header">
                    <h5><i class="bi-award"></i> Leadership</h5>
                    <a href="{{ url('cms/bod') }}" class="dash-link">Manage <i class="bi-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    @foreach (['Chairman' => $chairman, 'Registrar' => $registrar] as $role => $person)
                        <div class="dash-person">
                            <span class="dash-person-photo">
                                <img src="{{ $person?->getFirstMediaUrl('bod') ?: asset('frontend/images/avatar-placeholder.svg') }}" alt="{{ $person->title ?? '' }}">
                            </span>
                            <div>
                                <strong>{{ $person->title ?? 'Not assigned' }}</strong>
                                <span>{{ $person?->content ?: $role }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi-journal-text"></i> Notary Namespace</h5>
                    <span class="badge bg-light text-muted">Sample data</span>
                </div>
                <div class="card-body">
                    <div class="dash-directory">
                        @foreach ($directory as $item)
                            <a href="{{ $item['url'] }}" target="_blank" class="dash-directory-item">
                                <strong>{{ number_format($item['value']) }}</strong>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .dash-welcome {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.75rem 2rem;
            border-radius: 16px;
            background: linear-gradient(120deg, #003893 0%, #00296b 60%, #8a1538 140%);
            color: #fff;
            overflow: hidden;
        }

        .dash-welcome::after {
            content: "";
            position: absolute;
            right: -60px;
            top: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 45px solid rgba(255, 255, 255, 0.06);
            pointer-events: none;
        }

        .dash-welcome-text,
        .dash-welcome-actions {
            position: relative;
            z-index: 1;
        }

        .dash-welcome-date {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            opacity: 0.75;
        }

        .dash-welcome h2 {
            margin: 0.35rem 0 0.25rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
        }

        .dash-welcome p {
            margin: 0;
            opacity: 0.8;
        }

        .dash-welcome-actions {
            display: flex;
            gap: 0.6rem;
        }

        .dash-btn-accent {
            background-color: #dc143c;
            border-color: #dc143c;
            color: #fff;
        }

        .dash-btn-accent:hover {
            background-color: #b81032;
            color: #fff;
        }

        .dash-stat {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            height: 100%;
            padding: 1.1rem 1.15rem;
            border-radius: 14px;
            background-color: #fff;
            border: 1px solid #e9edf4;
            box-shadow: 0 2px 10px rgba(0, 32, 84, 0.05);
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        a.dash-stat[href]:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 32, 84, 0.1);
            color: inherit;
        }

        .dash-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            margin-bottom: 0.6rem;
            border-radius: 12px;
            font-size: 1.15rem;
            background-color: var(--dash-tone-bg);
            color: var(--dash-tone);
        }

        .dash-stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            color: #1f2937;
        }

        .dash-stat-label {
            font-size: 0.82rem;
            color: #6c757d;
        }

        .dash-tone-primary { --dash-tone: #003893; --dash-tone-bg: rgba(0, 56, 147, 0.1); }
        .dash-tone-danger { --dash-tone: #dc143c; --dash-tone-bg: rgba(220, 20, 60, 0.1); }
        .dash-tone-info { --dash-tone: #0e7490; --dash-tone-bg: rgba(14, 116, 144, 0.1); }
        .dash-tone-success { --dash-tone: #15803d; --dash-tone-bg: rgba(21, 128, 61, 0.1); }
        .dash-tone-warning { --dash-tone: #b45309; --dash-tone-bg: rgba(180, 83, 9, 0.1); }
        .dash-tone-secondary { --dash-tone: #6d28d9; --dash-tone-bg: rgba(109, 40, 217, 0.1); }

        .dash-card {
            border: 1px solid #e9edf4;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 32, 84, 0.05);
            overflow: hidden;
        }

        .dash-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eef1f6;
        }

        .dash-card-header h5 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
        }

        .dash-card-header h5 i {
            color: #003893;
        }

        .dash-link {
            font-size: 0.82rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .dash-news-item,
        .dash-message {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid #f1f3f8;
            transition: background-color 0.15s ease;
        }

        .dash-news-item:last-child,
        .dash-message:last-child {
            border-bottom: none;
        }

        .dash-news-item:hover,
        .dash-message:hover {
            background-color: #f8faff;
        }

        .dash-news-date {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 3.2rem;
            height: 3.2rem;
            border-radius: 12px;
            background-color: rgba(0, 56, 147, 0.08);
            color: #003893;
            line-height: 1.1;
        }

        .dash-news-date strong {
            font-size: 1.1rem;
        }

        .dash-news-date span {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dash-news-body,
        .dash-message-body {
            flex: 1;
            min-width: 0;
        }

        .dash-news-body h6 {
            margin: 0 0 0.3rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dash-news-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
        }

        .dash-news-actions {
            display: flex;
            gap: 0.35rem;
        }

        .dash-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .dash-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 1rem 0.5rem;
            border-radius: 12px;
            background-color: #f5f7fb;
            border: 1px solid #e9edf4;
            color: #374151;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .dash-action i {
            font-size: 1.3rem;
            color: #003893;
            transition: color 0.2s ease;
        }

        .dash-action:hover {
            transform: translateY(-2px);
            background-color: #003893;
            color: #fff;
        }

        .dash-action:hover i {
            color: #fff;
        }

        .dash-message {
            align-items: flex-start;
        }

        .dash-message-avatar {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(150deg, #003893, #dc143c);
            color: #fff;
            font-weight: 700;
        }

        .dash-message-head {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.85rem;
        }

        .dash-message-body p {
            margin: 0.2rem 0 0.35rem;
            font-size: 0.85rem;
            color: #4b5563;
        }

        .dash-message-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            font-size: 0.78rem;
        }

        .dash-person {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .dash-person + .dash-person {
            margin-top: 0.9rem;
        }

        .dash-person-photo {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            overflow: hidden;
            background-color: #eef1f6;
            border: 2px solid rgba(0, 56, 147, 0.15);
            color: #98a2b3;
            font-size: 1.2rem;
        }

        .dash-person-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
        }

        .dash-person strong {
            display: block;
            font-size: 0.9rem;
        }

        .dash-person span {
            font-size: 0.78rem;
            font-weight: 600;
            color: #dc143c;
        }

        .dash-directory {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.6rem;
        }

        .dash-directory-item {
            display: flex;
            flex-direction: column;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            background-color: #f5f7fb;
            color: inherit;
            transition: background-color 0.2s ease;
        }

        .dash-directory-item:hover {
            background-color: rgba(0, 56, 147, 0.08);
            color: inherit;
        }

        .dash-directory-item strong {
            font-size: 1.2rem;
            color: #003893;
        }

        .dash-directory-item span {
            font-size: 0.78rem;
            color: #6c757d;
        }

        .dash-empty {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #6c757d;
        }

        .dash-empty i {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 2rem;
            color: #c3cad6;
        }

        .dash-empty p {
            margin-bottom: 0.75rem;
        }

        @media (max-width: 575px) {
            .dash-welcome {
                padding: 1.4rem;
            }

            .dash-welcome h2 {
                font-size: 1.3rem;
            }
        }
    </style>
@endpush
