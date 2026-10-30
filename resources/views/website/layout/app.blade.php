<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'np' ? 'ne' : app()->getLocale() }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        {{ __('Notary Public Council') }}
    </title>
    {{-- <link rel="shortcut icon" href="{{ getSiteSetting('logo_image') }}"> --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('website.layout.style')
    @stack('styles')
</head>

<body>
    @include('website.layout.auth-header')
    @include('website.layout.breadcrumb')


    <div class="contain" style="min-height: 600px;">
        @yield('content')
    </div>

    @include('website.layout.auth-footer')
    @include('website.layout.script')

    @stack('scripts')
</body>

</html>
