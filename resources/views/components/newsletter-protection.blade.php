<x-honeypot name="website_url" :id="$honeypotId" class="sr-only" />

@if (config('services.turnstile.site_key'))
    <div class="flex justify-center">
        <x-turnstile :action="config('services.turnstile.newsletter_action')" theme="dark" />
    </div>
@endif

@php($newsletterErrors = $errors->getBag('newsletter'))

@if ($newsletterErrors->has('cf-turnstile-response'))
    <p role="alert" class="text-sm text-red-200">{{ $newsletterErrors->first('cf-turnstile-response') }}</p>
@endif

@if ($newsletterErrors->has('newsletter_rate_limit'))
    <p role="alert" class="text-sm text-red-200">{{ $newsletterErrors->first('newsletter_rate_limit') }}</p>
@endif
