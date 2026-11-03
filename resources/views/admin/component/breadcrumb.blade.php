<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    @isset($title)
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                    @endisset
                </ol>
            </div>
            <h4 class="page-title">{{ $title ?? __('Welcome!') }}</h4>
        </div>
    </div>
</div>
