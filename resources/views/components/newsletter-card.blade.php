<div
    id="newsletter"
    class="from-navy via-navy-light to-navy relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br p-7 text-center"
>
    @php
        $newsletterHasFeedback = $errors->newsletter->isNotEmpty() || session('newsletter_error');
    @endphp
    <div class="bg-gold/5 absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full blur-3xl"></div>
    <div class="relative">
        <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
            <svg class="text-gold size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
        </div>
        <h3 class="font-heading mb-2 text-lg font-bold text-white">Stay in the Loop</h3>
        <p class="mb-5 text-base/relaxed text-white/60 sm:text-sm/relaxed">
            {{ $subtitle ?? 'New posts & Disney tips delivered to your inbox' }}
        </p>

        @if (session('newsletter_success'))
            <p
                role="status"
                class="mb-3 rounded-lg border border-green-300/20 bg-green-300/10 px-3 py-2 text-base text-green-100 sm:text-sm"
            >
                You're subscribed! Check your inbox soon.
            </p>
        @elseif (session('newsletter_error'))
            <p
                role="alert"
                class="mb-3 rounded-lg border border-red-300/20 bg-red-300/10 px-3 py-2 text-base text-red-100 sm:text-sm"
            >
                {{ session('newsletter_error') }}
            </p>
        @endif

        <form action="{{ route('newsletter.store') }}" method="POST" class="space-y-3">
            @csrf
            <x-newsletter-protection honeypot-id="card-newsletter-website" />
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input
                id="newsletter-email"
                type="email"
                name="email"
                value="{{ $newsletterHasFeedback ? old('email') : '' }}"
                placeholder="your@email.com"
                autocomplete="email"
                required
                @error('email', 'newsletter') aria-invalid="true" aria-describedby="newsletter-email-error" @enderror
                class="focus:border-gold/40 focus:ring-gold/20 min-h-12 w-full rounded-xl border border-white/10 bg-white/8 px-4 py-3 text-base text-white transition-colors placeholder:text-white/60 focus:bg-white/12 focus:ring-2 focus:outline-none sm:text-sm"
            />
            @error('email', 'newsletter')
                <p id="newsletter-email-error" role="alert" class="text-left text-sm text-red-200">{{ $message }}</p>
            @enderror
            <button
                type="submit"
                class="from-gold to-gold-light text-navy shadow-gold/20 min-h-12 w-full rounded-full bg-linear-to-r py-3 text-base font-bold shadow-lg transition-transform hover:-translate-y-0.5 sm:text-sm"
            >
                Subscribe
            </button>
        </form>
        <p class="mt-3 text-base text-white/60 sm:text-xs">We use your email to send Mouse28 updates.</p>
    </div>
</div>
