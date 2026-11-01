@extends('admin.layout.app')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    @endphp

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);"> </a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Dashboard</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
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

        .dash-tone-primary {
            --dash-tone: #003893;
            --dash-tone-bg: rgba(0, 56, 147, 0.1);
        }

        .dash-tone-danger {
            --dash-tone: #dc143c;
            --dash-tone-bg: rgba(220, 20, 60, 0.1);
        }

        .dash-tone-info {
            --dash-tone: #0e7490;
            --dash-tone-bg: rgba(14, 116, 144, 0.1);
        }

        .dash-tone-success {
            --dash-tone: #15803d;
            --dash-tone-bg: rgba(21, 128, 61, 0.1);
        }

        .dash-tone-warning {
            --dash-tone: #b45309;
            --dash-tone-bg: rgba(180, 83, 9, 0.1);
        }

        .dash-tone-secondary {
            --dash-tone: #6d28d9;
            --dash-tone-bg: rgba(109, 40, 217, 0.1);
        }

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

        .dash-person+.dash-person {
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
