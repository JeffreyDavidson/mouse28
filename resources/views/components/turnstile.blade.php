@props(['action', 'theme'])

{{-- Each form passes its own Turnstile action, which the server checks against the token. --}}
@once('turnstile-api')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endonce
<div
    class="cf-turnstile"
    data-sitekey="{{ config('services.turnstile.site_key') }}"
    data-action="{{ $action }}"
    data-theme="{{ $theme }}"
    data-appearance="interaction-only"
></div>
