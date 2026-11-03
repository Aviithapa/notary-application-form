{{-- Sidebar logo: large when expanded, small when the sidebar is condensed. --}}
@php $logo = asset('frontend/images/logo.png'); @endphp

<!-- Brand Logo -->
<a href="{{ url('dashboard') }}" class="logo logo-dark d-block text-center" style="height: auto; padding: 16px 0;">
    <span class="logo-lg">
        <img src="{{ $logo }}" alt="Nepal Notary Public Council" style="height: 90px; width: auto;">
    </span>
    <span class="logo-sm">
        <img src="{{ $logo }}" alt="Nepal Notary Public Council" style="height: 40px; width: auto;">
    </span>
</a>
