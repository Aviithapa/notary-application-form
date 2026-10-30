{{-- Footer for standalone pages (login, errors): links point to the public council website, no database lookups --}}
@php
    $mainSite = rtrim(config('app.main_site_url'), '/');
    $siteTitle = __('Nepal Notary Public Council');
@endphp

<footer class="npc__footer">
    <div class="npc__footer-accent"></div>

    <div class="npc__container--fluid">
        <div class="npc__footer-grid npc__footer-grid--3">
            <!-- organisation -->
            <div class="npc__footer-brand">
                <div class="npc__footer-brand-head">
                    <div class="npc__footer-logo">
                        <img src="{{ asset('frontend/images/logo.png') }}" alt="{{ $siteTitle }}" />
                    </div>
                    <h2 class="npc__footer-brand-title">{{ $siteTitle }}</h2>
                </div>
                <p class="npc__footer-tagline">
                    {{ __('Regulating and supporting notary public services across Nepal with integrity and transparency.') }}
                </p>
                <div class="npc__footer-socials">
                    <a href="{{ $mainSite }}" target="_blank" rel="noopener" class="npc__footer-icon-btn"
                        aria-label="{{ __('Back to site') }}">
                        <i class="fa-solid fa-globe"></i>
                    </a>
                    <a href="mailto:nepalnotary@gmail.com" class="npc__footer-icon-btn" aria-label="{{ __('Email') }}">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                </div>
            </div>

            <!-- main site links -->
            <div>
                <h3 class="npc__footer-heading">{{ __('Quick Links') }}</h3>
                <ul class="npc__footer-list">
                    <li><a href="{{ $mainSite }}/">{{ __('Home') }}</a></li>
                    <li><a href="{{ $mainSite }}/about-notary/">{{ __('About Notary') }}</a></li>
                    <li><a href="{{ $mainSite }}/notices/">{{ __('Notices') }}</a></li>
                    <li><a href="{{ $mainSite }}/people/">{{ __('Council Members') }}</a></li>
                </ul>
            </div>

            <!-- contact -->
            <div>
                <h3 class="npc__footer-heading">{{ __('Contact Us') }}</h3>
                <ul class="npc__footer-contact">
                    <li>
                        <span class="npc__footer-contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <small>{{ __('Address') }}</small>
                            <span>{{ __('Kathmandu, Nepal') }}</span>
                        </div>
                    </li>
                    <li>
                        <span class="npc__footer-contact-icon"><i class="fa-solid fa-phone"></i></span>
                        <div>
                            <small>{{ __('Phone') }}</small>
                            <a href="tel:015452357">01-5452357</a>
                        </div>
                    </li>
                    <li>
                        <span class="npc__footer-contact-icon"><i class="fa-solid fa-user-tie"></i></span>
                        <div>
                            <small>{{ __('Information Officer') }}</small>
                            <a href="tel:9768537672">9768537672</a>
                        </div>
                    </li>
                    <li>
                        <span class="npc__footer-contact-icon"><i class="fa-solid fa-envelope"></i></span>
                        <div>
                            <small>{{ __('Email') }}</small>
                            <a href="mailto:nepalnotary@gmail.com">nepalnotary@gmail.com</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="npc__footer-bottom">
        <div class="npc__container--fluid npc__footer-bottom-inner">
            <p class="npc__copyright">
                &copy; {{ date('Y') }} {{ $siteTitle }}. {{ __('All rights reserved.') }}
            </p>
            <a href="{{ $mainSite }}" class="npc__footer-scroll-top">
                <span>{{ __('Back to site') }}</span>
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>
</footer>
