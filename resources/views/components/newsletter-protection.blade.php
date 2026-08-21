<div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
    <label for="{{ $honeypotId }}">Website</label>
    <input id="{{ $honeypotId }}" type="text" name="website_url" tabindex="-1" autocomplete="off">
</div>

@if (config('services.turnstile.site_key'))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
    <div class="flex justify-center">
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-action="{{ config('services.turnstile.newsletter_action') }}"
            data-theme="dark"
        ></div>
    </div>
@endif

@error('cf-turnstile-response')
    <p role="alert" class="text-sm text-red-200">{{ $message }}</p>
@enderror

@error('newsletter_rate_limit')
    <p role="alert" class="text-sm text-red-200">{{ $message }}</p>
@enderror
