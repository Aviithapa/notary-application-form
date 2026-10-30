{{-- Slim header for standalone pages (login, errors): logo, title and language switch, no navigation --}}
@php $isNepali = app()->getLocale() === 'np'; @endphp
<header class="npc__header-middle-nav">
    <div class="npc__container--fluid">
        <div class="npc__header-mid-wrapper">
            <div class="npc__header-logo">
                <a href="{{ url('/') }}">
                    <div class="npc__logo-wrapper">
                        <img src="{{ asset('frontend/images/logo.png') }}" alt="notary public council logo" />
                    </div>
                </a>
            </div>

            <div class="npc__header-title">
                <a href="{{ url('/') }}">{{ __('Nepal Notary Public Council') }}</a>
                <span class="npc__header-subtitle">{{ __('Kathmandu, Nepal') }}</span>
            </div>

            <div class="npc__auth-header-actions">
                <div class="npc__nepal-flag">
                    <div class="npc__nepal-flag-wrapper">
                        <img src="{{ asset('frontend/images/np_flag.gif') }}" alt="flag of nepal" />
                    </div>
                </div>
                <a href="{{ config('app.main_site_url') }}" class="npc__back-site">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>{{ __('Back to site') }}</span>
                </a>
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
</header>
