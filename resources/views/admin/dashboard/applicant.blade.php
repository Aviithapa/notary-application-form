@extends('admin.layout.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Welcome!') }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ __('Your account is ready') }}</h5>
                    <p class="text-muted mb-3">
                        {{ __('You are signed in as') }} <strong>{{ $user->email }}</strong>.
                        {{ __('Your email address has been verified.') }}
                    </p>
                    <span class="badge bg-success-subtle text-success">{{ __('Applicant') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
