{{--
    Page hero + breadcrumb shown on every inner page.
    Pages can customise it with optional sections:
      @section('page_title', '...')        defaults to $pageData->title
      @section('page_subtitle', '...')     defaults to the site title
      @section('page_parent', '...')       middle crumb label (with page_parent_url)
      @section('page_parent_url', '...')
      @section('hero_image', '...')        defaults to frontend/images/breadcrumb.jpg
--}}
@php
    $isHome = ($slug ?? null) === 'index' || request()->is('/');
    $heroTitle = View::hasSection('page_title')
        ? View::getSection('page_title')
        : (isset($pageData->title) ? e(__($pageData instanceof \App\Models\Post ? $pageData->localized('title') : $pageData->title)) : null);
@endphp

@if (!$isHome && $heroTitle)
    <section class="npc__page-hero"
        style="--page-hero-image: url('{{ View::hasSection('hero_image') ? View::getSection('hero_image') : asset('frontend/images/breadcrumb.jpg') }}')">
        <div class="npc__container--fluid">
            <div class="npc__page-hero-content">
                <nav class="npc__page-hero-crumbs" aria-label="breadcrumb">
                    <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> {{ __('Home') }}</a>
                    @if (View::hasSection('page_parent'))
                        <i class="fa-solid fa-chevron-right"></i>
                        <a href="{{ View::hasSection('page_parent_url') ? View::getSection('page_parent_url') : '#' }}">@yield('page_parent')</a>
                    @endif
                    <i class="fa-solid fa-chevron-right"></i>
                    <span aria-current="page">{!! $heroTitle !!}</span>
                </nav>
                <h1>{!! $heroTitle !!}</h1>
                <p>
                    @hasSection('page_subtitle')
                        @yield('page_subtitle')
                    @else
                        {{ getSiteSetting('site_title') ?: __('Notary Public Council') }}
                    @endif
                </p>
            </div>
        </div>
    </section>
@endif
