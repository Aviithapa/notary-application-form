@php
  $siteTitle = getSiteSetting('site_title') ?: __('Notary Public Council');
  $socials = array_filter([
      'fa-brands fa-facebook-f' => getSiteSetting('social_fb'),
      'fa-brands fa-x-twitter' => getSiteSetting('social_twitter'),
      'fa-brands fa-youtube' => getSiteSetting('social_youtube'),
      'fa-brands fa-instagram' => getSiteSetting('social_instagram'),
  ]);
@endphp

<footer class="npc__footer">
  <div class="npc__footer-accent"></div>

  <div class="npc__container--fluid">
    <div class="npc__footer-grid">
      <!-- organisation -->
      <div class="npc__footer-brand">
        <div class="npc__footer-brand-head">
          <div class="npc__footer-logo">
            <img src="{{ getSiteSetting('logo_image') }}" alt="{{ $siteTitle }}" />
          </div>
          <h2 class="npc__footer-brand-title">{{ $siteTitle }}</h2>
        </div>
        <p class="npc__footer-tagline">
          {{ __('Regulating and supporting notary public services across Nepal with integrity and transparency.') }}
        </p>
        @if (count($socials) || getSiteSetting('email'))
          <div class="npc__footer-socials">
            @foreach ($socials as $icon => $link)
              <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="npc__footer-icon-btn">
                <i class="{{ $icon }}"></i>
              </a>
            @endforeach
            @if (getSiteSetting('email'))
              <a href="mailto:{{ getSiteSetting('email') }}" class="npc__footer-icon-btn">
                <i class="fa-solid fa-envelope"></i>
              </a>
            @endif
          </div>
        @endif
      </div>

      <!-- site links -->
      <div>
        <h3 class="npc__footer-heading">{{ __('Explore') }}</h3>
        <ul class="npc__footer-list">
          <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
          <li><a href="{{ url('about') }}">{{ __('Introduction') }}</a></li>
          <li><a href="{{ url('bod') }}">{{ __('Board Member') }}</a></li>
          <li><a href="{{ url('staff') }}">{{ __('Staff') }}</a></li>
          <li><a href="{{ url('news') }}">{{ __('News & Notice') }}</a></li>
          <li><a href="{{ route('legal.documents') }}">{{ __('Legal Documents') }}</a></li>
          <li><a href="{{ url('contact') }}">{{ __('Contact') }}</a></li>
        </ul>
      </div>

      <div>
        <h3 class="npc__footer-heading">{{ __('Notary Namespace') }}</h3>
        <ul class="npc__footer-list">
          <li><a href="{{ route('directory.notaries') }}">{{ __('Notary Public List') }}</a></li>
          <li><a href="{{ route('directory.district-change') }}">{{ __('District Change') }}</a></li>
          <li><a href="{{ route('directory.translators') }}">{{ __('Translators List') }}</a></li>
          <li><a href="{{ route('directory.cancelled-translators') }}">{{ __('Cancelled Translators') }}</a></li>
        </ul>
      </div>

      <!-- contact -->
      <div>
        <h3 class="npc__footer-heading">{{ __('Contact Us') }}</h3>
        <ul class="npc__footer-contact">
          @if (getSiteSetting('location'))
            <li>
              <span class="npc__footer-contact-icon"><i class="fa-solid fa-location-dot"></i></span>
              <div>
                <small>{{ __('Address') }}</small>
                <span>{{ localizedSiteSetting('location') }}</span>
              </div>
            </li>
          @endif
          @if (getSiteSetting('social_phone'))
            <li>
              <span class="npc__footer-contact-icon"><i class="fa-solid fa-phone"></i></span>
              <div>
                <small>{{ __('Phone') }}</small>
                <a href="tel:{{ getSiteSetting('social_phone') }}">{{ getSiteSetting('social_phone') }}</a>
              </div>
            </li>
          @endif
          @if (getSiteSetting('email'))
            <li>
              <span class="npc__footer-contact-icon"><i class="fa-solid fa-envelope"></i></span>
              <div>
                <small>{{ __('Email') }}</small>
                <a href="mailto:{{ getSiteSetting('email') }}">{{ getSiteSetting('email') }}</a>
              </div>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>

  <div class="npc__footer-bottom">
    <div class="npc__container--fluid npc__footer-bottom-inner">
      <p class="npc__copyright">
        {{ getSiteSetting('copy_right') ?: 'Copyright © ' . date('Y') . ' ' . $siteTitle . '. ' . __('All rights reserved.') }}
      </p>
      <button
        type="button"
        class="npc__footer-scroll-top"
        aria-label="{{ __('Back to top') }}"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
      >
        <span>{{ __('Back to top') }}</span>
        <i class="fa-solid fa-arrow-up"></i>
      </button>
    </div>
  </div>
</footer>
