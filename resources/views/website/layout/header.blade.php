@php
    // Highlights the menu entry for the page being viewed, e.g. $navActive('news', 'news-details/*')
    $navActive = fn(...$patterns) => request()->is(...$patterns) ? 'is-active' : '';
    $aboutPages = ['about', 'bod', 'staff'];
    $notaryPages = ['notary-public-list', 'district-change-notary-public', 'translators', 'cancelled-translators'];
    $requirementPages = ['requirement-diploma', 'requirement-bachelor'];
@endphp
<!-- header  -->
<header>
    <!-- TOP NAV -->
    <div class="npc__header-top-nav">
        <div class="npc__container--fluid px-2 flex justify-between">
            <!-- contact info  -->
            <div class="npc__header-contact flex">
                <div class="npc__header-contact-phone npc__contact-child flex items-center gap-1">
                    <span>
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <span>{{ getSiteSetting('social_phone') }} </span>
                </div>
                <div class="npc__header-contact-email npc__contact-child flex items-center gap-1">
                    <span>
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <span> {{ getSiteSetting('email') }} </span>
                </div>
            </div>
            <!-- socail links  -->
            <div class="npc__header-social flex items-center">

                <span> {{ \Carbon\Carbon::now()->translatedFormat('d M, Y') }} </span>
                |
                @php $isNepali = app()->getLocale() === 'np'; @endphp
                <a href="{{ route('lang.switch', $isNepali ? 'en' : 'np') }}"
                    class="npc__lang-switch {{ $isNepali ? 'is-nepali' : '' }}" role="switch"
                    aria-checked="{{ $isNepali ? 'true' : 'false' }}" aria-label="{{ __('Language') }}">
                    <span class="npc__lang-switch-label">EN</span>
                    <span class="npc__lang-switch-track"><span class="npc__lang-switch-thumb"></span></span>
                    <span class="npc__lang-switch-label">नेपाली</span>
                </a>
            </div>
        </div>
    </div>
    <!-- MIDDLE NAV  -->
    <div class="npc__header-middle-nav">
        <div class="npc__container--fluid">
            <!-- HEADER LOGO  -->
            <div class="npc__header-mid-wrapper">
                <div class="npc__header-logo">
                    <a href="/">
                        <div class="npc__logo-wrapper">
                            <img src="{{ getSiteSetting('logo_image') }}" alt="notary public council logo" />
                        </div>
                    </a>
                </div>

                <div class="npc__header-title">
                    <a href="/">{{ __('Nepal Notary Public Council') }}</a>
                    <a href="/" class="npc__header-subtitle">{{ localizedSiteSetting('location') ?: __('Kathmandu, Nepal') }}</a>
                </div>

                <div class="npc__nepal-flag">
                    <div class="npc__nepal-flag-wrapper">
                        <img src="{{ asset('frontend/images/np_flag.gif') }}" alt="flag of nepal" />
                    </div>
                </div>
                <!-- MOBILE NAV TOGGLE BUTTON -->
                <div class="npc__header-mob-toggle">
                    <div class="npc__header-mob-toggle-icon-container">
                        <button class="hamburger-icon" id="hamburger">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <button class="cross-icon" id="cross">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section>
        <div class="npc__announcement-container--fluid flex">
            <div class="npc__announcement-title flex items-center justify-center">
                <h2 class="uppercase">{{ __('News') }}</h2>
            </div>
            <div class="announcement__marquee-container flex-1 overflow-hidden flex items-center">
                <div class="announcement__marquee inline-block whitespace-nowrap">
                    <p class="announcement__marquee-content cursor-pointer flex gap-4 items-center">
                        @isset($news)
                            @if (
                                $news instanceof \Illuminate\Support\Collection ||
                                    $news instanceof \Illuminate\Database\Eloquent\Collection ||
                                    $news instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                @foreach ($news as $new)
                                    <a href="{{ url('news-details/' . $new->id) }}" class="blinking-text">
                                        {{ $new->localized('title') }}
                                    </a>
                                @endforeach
                            @else
                                <a href="{{ url('news-details/' . $news->id) }}" class="blinking-text">
                                    {{ $news->localized('title') }}
                                </a>
                            @endif
                        @endisset



                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- MAIN NAV  -->
    <div class="npc__header-main-nav">
        <div class="npc__header-container px-2">
            <nav class="flex items-center">
                <!-- nav items  -->
                <!-- normal link  -->
                <div class="npc__nav-item">
                    <a class="npc__nav-btn inline-block {{ $navActive('/') }}" href="/">{{ __('Home') }}</a>
                </div>
                <!-- dropdown sample  -->
                <div class="npc__nav-item relative">
                    <button class="npc__nav-btn npc__show-dropdown flex items-center transparent gap-1 {{ $navActive(...$aboutPages) }}">
                        <span> {{ __('About') }} </span>
                        <span class="npc__dropdown-icon">
                            <i class="fa-solid fa-angle-down"></i>
                        </span>
                    </button>
                    <div class="npc__dropdown invisible absolute left-0 top-full">
                        <nav class="npc__dropdown-links flex flex-col">
                            <a href="{{ url('about') }}" class="{{ $navActive('about') }}">{{ __('Introduction') }}</a>
                            <a href="{{ url('bod') }}" class="{{ $navActive('bod') }}">{{ __('Board Member') }}</a>
                            <a href="{{ url('staff') }}" class="{{ $navActive('staff') }}">{{ __('Staffs') }}</a>
                        </nav>
                    </div>
                </div>

                <div class="npc__nav-item relative">
                    <button class="npc__nav-btn npc__show-dropdown flex items-center transparent gap-1 {{ $navActive(...$notaryPages) }}">
                        <span> {{ __('Notary Namespace') }} </span>
                        <span class="npc__dropdown-icon">
                            <i class="fa-solid fa-angle-down"></i>
                        </span>
                    </button>
                    <div class="npc__dropdown invisible absolute left-0 top-full">
                        <nav class="npc__dropdown-links flex flex-col">
                            <a href="{{ url('notary-public-list') }}"
                                class="{{ $navActive('notary-public-list') }}">{{ __('Notary Public List') }}</a>
                            <a href="{{ url('district-change-notary-public') }}"
                                class="{{ $navActive('district-change-notary-public') }}">{{ __('District Change (Notary Public List)') }}</a>
                            <a href="{{ url('translators') }}" class="{{ $navActive('translators') }}">{{ __('Translators List') }}</a>
                            <a href="{{ url('cancelled-translators') }}"
                                class="{{ $navActive('cancelled-translators') }}">{{ __('Cancelled Translators List') }}</a>


                        </nav>
                    </div>
                </div>




                <div class="npc__nav-item">
                    <a class="npc__nav-btn inline-block {{ $navActive('news', 'news-details/*') }}" href="{{ url('news') }}">{{ __('News & Notice') }}</a>
                </div>


                <div class="npc__nav-item">
                    <a class="npc__nav-btn inline-block {{ $navActive('legal-documents') }}"
                        href="{{ url('legal-documents') }}">{{ __('Legal Documents') }}</a>
                </div>

                <div class="npc__nav-item">
                    <a class="npc__nav-btn inline-block {{ $navActive('contact') }}" href="{{ url('contact') }}">{{ __('Contact') }}</a>
                </div>







            </nav>
        </div>
    </div>
    <!-- MOBILE NAV  -->
    <div class="npc__mob-nav" id="mobile-nav">
        <nav class="npc__mob-nav-links">

            <div class="npc__mob-nav-link">
                <a class="npc__mob-nav-item {{ $navActive('/') }}" href="/">{{ __('Home') }}</a>
            </div>
            <!-- dropdown sample  -->

            <div class="npc__mob-nav-link npc__nav-link-dropdown {{ request()->is(...$aboutPages) ? 'npc__mob-dropdown-icon' : 'npc__mob-dropdown-hide' }}">
                <button class="npc__mob-nav-item npc__mob-nav-drop-item {{ $navActive(...$aboutPages) }}">
                    <span> {{ __('About') }} </span>
                    <span class="npc__mob-drop-right-icon">
                        <i class="fa-solid fa-angle-right"></i>
                    </span>
                    <span class="npc__mob-drop-down-icon">
                        <i class="fa-solid fa-angle-down"></i>
                    </span>
                </button>
                <div class="npc__mob-dropdown">
                    <nav class="npc__mob-dropdown-links">
                        <a href="/about" class="npc__mob-dropdown-item {{ $navActive('about') }}">{{ __('About') }}</a>
                        <a href="/staff" class="npc__mob-dropdown-item {{ $navActive('staff') }}">{{ __('Staffs') }}</a>
                        <a href="/bod" class="npc__mob-dropdown-item {{ $navActive('bod') }}">{{ __('Board Members') }}</a>
                    </nav>
                </div>
            </div>


            <div class="npc__mob-nav-link npc__nav-link-dropdown {{ request()->is(...$notaryPages) ? 'npc__mob-dropdown-icon' : 'npc__mob-dropdown-hide' }}">
                <button class="npc__mob-nav-item npc__mob-nav-drop-item {{ $navActive(...$notaryPages) }}">
                    <span> {{ __('Notary Namespace') }} </span>
                    <span class="npc__mob-drop-right-icon">
                        <i class="fa-solid fa-angle-right"></i>
                    </span>
                    <span class="npc__mob-drop-down-icon">
                        <i class="fa-solid fa-angle-down"></i>
                    </span>
                </button>
                <div class="npc__mob-dropdown">
                    <nav class="npc__mob-dropdown-links">
                        <a href="{{ url('notary-public-list') }}" class="npc__mob-dropdown-item {{ $navActive('notary-public-list') }}">{{ __('Notary Public List') }}</a>
                        <a href="{{ url('district-change-notary-public') }}" class="npc__mob-dropdown-item {{ $navActive('district-change-notary-public') }}">{{ __('District Change (Notary Public List)') }}</a>
                        <a href="{{ url('translators') }}" class="npc__mob-dropdown-item {{ $navActive('translators') }}">{{ __('Translators List') }}</a>
                        <a href="{{ url('cancelled-translators') }}" class="npc__mob-dropdown-item {{ $navActive('cancelled-translators') }}">{{ __('Cancelled Translators List') }}</a>
                    </nav>
                </div>
            </div>

            <div class="npc__mob-nav-link npc__nav-link-dropdown {{ request()->is(...$requirementPages) ? 'npc__mob-dropdown-icon' : 'npc__mob-dropdown-hide' }}">
                <button class="npc__mob-nav-item npc__mob-nav-drop-item {{ $navActive(...$requirementPages) }}">
                    <span> {{ __('Minimum Requirement') }} </span>
                    <span class="npc__mob-drop-right-icon">
                        <i class="fa-solid fa-angle-right"></i>
                    </span>
                    <span class="npc__mob-drop-down-icon">
                        <i class="fa-solid fa-angle-down"></i>
                    </span>
                </button>
                <div class="npc__mob-dropdown">
                    <nav class="npc__mob-dropdown-links">
                        <a href="/requirement-diploma"
                            class="npc__mob-dropdown-item {{ $navActive('requirement-diploma') }}">{{ __('Diploma Level Requirement') }}</a>
                        <a href="/requirement-bachelor"
                            class="npc__mob-dropdown-item {{ $navActive('requirement-bachelor') }}">{{ __('Bachelor Level Requirement') }}</a>
                    </nav>
                </div>
            </div>



            <div class="npc__mob-nav-link">
                <a class="npc__mob-nav-item {{ $navActive('news', 'news-details/*') }}" href="/news">{{ __('News & Notice') }}</a>
            </div>

            <div class="npc__mob-nav-link">
                <a class="npc__mob-nav-item {{ $navActive('legal-documents') }}" href="/legal-documents">{{ __('Legal Documents') }}</a>
            </div>

            <div class="npc__mob-nav-link">
                <a class="npc__mob-nav-item {{ $navActive('contact') }}" href="{{ url('contact') }}">{{ __('Contact') }}</a>
            </div>



            <div class="npc__mob-nav-link">
                @foreach (config('app.available_locales') as $code => $label)
                    @continue(app()->getLocale() === $code)
                    <a class="npc__mob-nav-item" href="{{ route('lang.switch', $code) }}">{{ $label }}</a>
                @endforeach
            </div>

            <!-- drop down link  -->

            <!-- dorpdown 2  -->
        </nav>
    </div>
</header>
