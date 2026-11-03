<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel = "icon" class="img-fluid" href ="" type = "image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ __('Nepal Notary Public Council') }}
        {{ isset($pageData->title) ? '| ' . $pageData->title : '' }}
    </title>
    <link rel="shortcut icon" href="{{ asset('frontend/images/logo.png') }}">
    @include('admin.layout.style')
    @stack('styles')
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->

<body>
    <div class="wrapper">
        @if (in_array(Auth::user()->mainRole()?->name, [\App\Enums\RoleEnum::ADMIN, \App\Enums\RoleEnum::SUPER_ADMIN], true))
            @include('admin.sidebar.sidebar')
        @else
            @include('admin.sidebar.default')
        @endif

        <div class="content-page">

            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">


                    @yield('content')


                </div>
                <!-- container -->

            </div>
            <!-- content -->

        </div>
    </div>
    @include('admin.layout.script')
    @stack('scripts')
</body>

</html>
