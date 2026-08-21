<div id="newsletter" class="relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br from-navy via-navy-light to-navy p-7 text-center">
    <div class="absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full bg-gold/5 blur-3xl"></div>
    <div class="relative">
        <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
            <svg class="size-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        </div>
        <h3 class="mb-2 font-heading text-lg font-bold text-white">Stay in the Loop</h3>
        <p class="mb-5 text-sm/relaxed text-white/40">{{ $subtitle ?? 'New posts & Disney tips delivered to your inbox' }}</p>

        @if(session('newsletter_success'))
            <p role="status" class="mb-3 rounded-lg border border-green-300/20 bg-green-300/10 px-3 py-2 text-sm text-green-100">
                You're subscribed! Check your inbox soon.
            </p>
        @elseif(session('newsletter_error'))
            <p role="alert" class="mb-3 rounded-lg border border-red-300/20 bg-red-300/10 px-3 py-2 text-sm text-red-100">
                {{ session('newsletter_error') }}
            </p>
        @endif

        <form action="/newsletter" method="POST" class="space-y-3">
            @csrf
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" autocomplete="email" required
                @error('email') aria-invalid="true" aria-describedby="newsletter-email-error" @enderror
                class="min-h-12 w-full rounded-xl border border-white/10 bg-white/8 px-4 py-3 text-base text-white transition-colors placeholder:text-white/25 focus:border-gold/40 focus:bg-white/12 focus:ring-2 focus:ring-gold/20 focus:outline-none sm:text-sm"
            >
            @error('email')
                <p id="newsletter-email-error" role="alert" class="text-left text-sm text-red-200">{{ $message }}</p>
            @enderror
            <button type="submit" class="min-h-12 w-full rounded-full bg-linear-to-r from-gold to-gold-light py-3 text-sm font-bold text-navy shadow-lg shadow-gold/20 transition-all hover:-translate-y-0.5">
                Subscribe
            </button>
        </form>
        <p class="mt-3 text-[10px] text-white/20">No spam. Unsubscribe anytime.</p>
    </div>
</div>
