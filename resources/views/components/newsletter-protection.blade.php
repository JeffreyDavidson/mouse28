<div class="sr-only" aria-hidden="true">
    <label for="{{ $honeypotId }}">Website</label>
    <input id="{{ $honeypotId }}" type="text" name="website_url" tabindex="-1" autocomplete="off" />
</div>

@if (config('services.turnstile.site_key'))
    @once('turnstile-api')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
    <div class="flex justify-center">
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-action="{{ config('services.turnstile.newsletter_action') }}"
            data-theme="dark"
            data-appearance="interaction-only"
        ></div>
    </div>
@endif

@php($newsletterErrors = $errors->getBag('newsletter'))

@if ($newsletterErrors->has('cf-turnstile-response'))
    <p role="alert" class="text-sm text-red-200">{{ $newsletterErrors->first('cf-turnstile-response') }}</p>
@endif

@if ($newsletterErrors->has('newsletter_rate_limit'))
    <p role="alert" class="text-sm text-red-200">{{ $newsletterErrors->first('newsletter_rate_limit') }}</p>
@endif
