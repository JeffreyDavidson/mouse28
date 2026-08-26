<x-layouts.app
    title="Contact — Mouse28"
    description="Contact Jeffrey and Cassie about Mouse28, Disney park accessibility, family travel, collaborations, or the podcast."
    og-title="Contact Mouse28"
    og-description="Get in touch with Jeffrey and Cassie about Disney park accessibility, family travel, collaborations, or the Mouse28 podcast."
    :canonical="route('contact.show')"
>
    @php
        $contactHasFeedback = $errors->contact->isNotEmpty();
    @endphp
    @if (session('success'))
        <section class="from-cream to-cream relative flex min-h-[70vh] items-center justify-center overflow-hidden bg-linear-to-br via-white">
            <div class="relative z-10 px-4 text-center">
                <div class="relative mx-auto mb-8 size-24">
                    <svg class="size-24" viewBox="0 0 96 96">
                        <circle cx="48" cy="48" r="44" fill="none" stroke="#5b3e9e" stroke-width="3" opacity="0.15" />
                        <circle
                            cx="48"
                            cy="48"
                            r="44"
                            fill="none"
                            stroke="#5b3e9e"
                            stroke-width="3"
                            stroke-dasharray="276.46"
                            stroke-dashoffset="276.46"
                            stroke-linecap="round"
                            class="contact-check-circle"
                        />
                        <path
                            d="M30 50 l12 12 l24 -28"
                            fill="none"
                            stroke="#d4a843"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-dasharray="80"
                            stroke-dashoffset="80"
                            class="contact-check-mark"
                        />
                    </svg>
                </div>
                <h1 class="font-heading text-navy mb-4 text-4xl font-bold md:text-5xl">Message Sent!</h1>
                <p class="text-navy/60 mx-auto mb-2 max-w-md text-lg">
                    Thank you for reaching out. We'll get back to you within 48 hours.
                </p>
                <p class="text-navy/65 mb-8 text-sm">In the meantime, feel free to explore our blog and podcast.</p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a
                        href="{{ route('blog.index') }}"
                        class="bg-purple hover:bg-purple-dark inline-flex items-center gap-2 rounded-full px-6 py-3 font-semibold text-white transition-[transform,background-color,box-shadow] hover:-translate-y-0.5 hover:shadow-lg"
                    >Read our blog</a>
                    <a
                        href="{{ route('episodes.index') }}"
                        class="text-navy/60 hover:text-purple inline-flex items-center gap-2 font-medium transition-colors"
                    >Listen to podcast →</a>
                </div>
            </div>
        </section>
    @else
        {{-- Full-width dark hero with form embedded --}}
        <section class="from-navy via-navy-light to-navy relative overflow-hidden bg-linear-to-br">
            {{-- Ambient glows --}}
            <div class="pointer-events-none absolute top-[-20%] right-[-5%] size-[700px] bg-[radial-gradient(circle,rgb(212_168_67/5%)_0%,transparent_60%)]"></div>
            <div class="pointer-events-none absolute bottom-[-30%] left-[-10%] size-[500px] bg-[radial-gradient(circle,rgb(91_62_158/15%)_0%,transparent_60%)]"></div>

            <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 md:py-24">
                {{-- Header --}}
                <div class="mb-14 text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                        <span class="bg-gold size-2 animate-pulse rounded-full"></span>
                        <span class="text-gold text-sm font-semibold tracking-widest uppercase">Get in Touch</span>
                    </div>
                    <h1 class="font-heading mt-2 text-4xl font-bold text-white md:text-5xl lg:text-6xl">
                        We'd Love to Hear From You
                    </h1>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-white/60">
                        Have a question about DAS, planning a Disney trip, or just want to say hi? We read every
                        message.
                    </p>
                </div>

                {{-- Two-column layout --}}
                <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px]">
                    {{-- Form --}}
                    <div class="border-cream/10 bg-cream/3 rounded-2xl border p-5 backdrop-blur-sm sm:p-8 md:p-10">
                        <form action="{{ route('contact.store') }}" method="POST" class="flex flex-col gap-6">
                            @csrf
                            @if (config('services.turnstile.site_key'))
                                @once('turnstile-api')
                                    <script
                                        src="https://challenges.cloudflare.com/turnstile/v0/api.js"
                                        async
                                        defer
                                    ></script>
                                @endonce
                            @endif
                            @if ($errors->contact->has('contact_rate_limit'))
                                <div
                                    role="alert"
                                    class="rounded-xl border border-red-400/25 bg-red-400/10 px-4 py-3 text-base text-red-200 sm:text-sm"
                                >
                                    {{ $errors->contact->first('contact_rate_limit') }}
                                </div>
                            @endif

                            <!-- Honeypot - hidden from humans, bots fill this -->
                            <div class="absolute top-[-9999px] left-[-9999px]" aria-hidden="true">
                                <label for="website_url">Website</label>
                                <input
                                    type="text"
                                    id="website_url"
                                    name="website_url"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label
                                        for="name"
                                        class="text-gold-light mb-2 block text-base font-semibold sm:text-sm"
                                    >Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        value="{{ $contactHasFeedback ? old('name') : '' }}"
                                        placeholder="Your name"
                                        @error('name', 'contact') aria-invalid="true" aria-describedby="name-error" @enderror
                                        class="border-cream/10 bg-cream/4 text-cream placeholder:text-cream/60 focus:border-gold/50 focus:bg-cream/6 focus:ring-gold/20 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none sm:text-sm"
                                    />
                                    @error('name', 'contact')
                                        <p id="name-error" role="alert" class="mt-2 text-sm text-red-400">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label
                                        for="email"
                                        class="text-gold-light mb-2 block text-base font-semibold sm:text-sm"
                                    >Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        required
                                        autocomplete="email"
                                        inputmode="email"
                                        value="{{ $contactHasFeedback ? old('email') : '' }}"
                                        placeholder="you@example.com"
                                        @error('email', 'contact') aria-invalid="true" aria-describedby="email-error" @enderror
                                        class="border-cream/10 bg-cream/4 text-cream placeholder:text-cream/60 focus:border-gold/50 focus:bg-cream/6 focus:ring-gold/20 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none sm:text-sm"
                                    />
                                    @error('email', 'contact')
                                        <p id="email-error" role="alert" class="mt-2 text-sm text-red-400">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label
                                    for="subject"
                                    class="text-gold-light mb-2 block text-base font-semibold sm:text-sm"
                                >Topic</label>
                                <select
                                    id="subject"
                                    name="subject"
                                    required
                                    @error('subject', 'contact') aria-invalid="true" aria-describedby="subject-error" @enderror
                                    class="contact-select border-cream/10 bg-cream/4 text-cream/70 focus:border-gold/50 focus:ring-gold/20 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none sm:text-sm"
                                >
                                    <option value="">Choose a topic...</option>
                                    <option
                                        value="general"
                                        @selected($contactHasFeedback && old('subject') === 'general')
                                    >
                                        General Question
                                    </option>
                                    <option
                                        value="accessibility"
                                        @selected($contactHasFeedback && old('subject') === 'accessibility')
                                    >
                                        Park Accessibility Question
                                    </option>
                                    <option
                                        value="collaboration"
                                        @selected($contactHasFeedback && old('subject') === 'collaboration')
                                    >
                                        Collaboration / Sponsorship
                                    </option>
                                    <option value="guest" @selected($contactHasFeedback && old('subject') === 'guest')>
                                        Guest on the Podcast
                                    </option>
                                    <option value="other" @selected($contactHasFeedback && old('subject') === 'other')>
                                        Other
                                    </option>
                                </select>
                                @error('subject', 'contact')
                                    <p id="subject-error" role="alert" class="mt-2 text-sm text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="message"
                                    class="text-gold-light mb-2 block text-base font-semibold sm:text-sm"
                                >Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    required
                                    rows="5"
                                    placeholder="What's on your mind?"
                                    @error('message', 'contact') aria-invalid="true" aria-describedby="message-error" @enderror
                                    class="border-cream/10 bg-cream/4 text-cream placeholder:text-cream/60 focus:border-gold/50 focus:bg-cream/6 focus:ring-gold/20 min-h-36 w-full resize-y rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none sm:text-sm"
                                >{{ $contactHasFeedback ? old('message') : '' }}</textarea>
                                @error('message', 'contact')
                                    <p id="message-error" role="alert" class="mt-2 text-sm text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                @if (config('services.turnstile.site_key'))
                                    <div
                                        class="cf-turnstile"
                                        data-sitekey="{{ config('services.turnstile.site_key') }}"
                                        data-action="{{ config('services.turnstile.contact_action') }}"
                                        data-theme="dark"
                                        data-appearance="interaction-only"
                                    ></div>
                                @else
                                    <p role="alert" class="text-base text-red-200 sm:text-sm">
                                        Contact verification is temporarily unavailable. Please try again later.
                                    </p>
                                @endif
                                @error('cf-turnstile-response', 'contact')
                                    <p id="turnstile-error" role="alert" class="mt-2 text-sm text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="from-gold to-gold-dark text-navy hover:shadow-gold/30 focus-visible:outline-gold min-h-12 w-full rounded-xl bg-linear-to-br px-6 py-3 text-base font-semibold transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-lg sm:text-sm"
                            >
                                Send Message
                            </button>
                        </form>
                    </div>

                    {{-- Sidebar --}}
                    <div class="flex flex-col gap-6">
                        {{-- Email card --}}
                        <div class="border-cream/8 bg-cream/4 rounded-2xl border p-6">
                            <div class="mb-5 flex items-center gap-[0.85rem]">
                                <div class="bg-gold/12 flex size-10 shrink-0 items-center justify-center rounded-[0.6rem]">
                                    <svg class="text-gold-light size-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <a
                                        href="mailto:{{ $contactEmail }}"
                                        class="text-cream inline-flex min-h-12 items-center text-base font-semibold break-all sm:min-h-6 sm:text-sm"
                                    >{{ $contactEmail }}</a>
                                    <p class="text-cream/60 mt-1 text-base sm:text-sm">We read every message</p>
                                </div>
                            </div>
                            <div class="bg-cream/4 flex items-center gap-2 rounded-[0.6rem] px-[0.85rem] py-[0.65rem]">
                                <svg class="text-gold-light size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-cream/60 text-base sm:text-sm">
                                    Typically within <strong class="text-cream/80">48 hours</strong>
                                </p>
                            </div>
                        </div>

                        {{-- Topics we love --}}
                        <div class="border-cream/8 bg-cream/4 rounded-2xl border p-6">
                            <h2 class="font-heading text-cream mb-4 text-base font-bold">We Love Hearing About</h2>
                            <div class="flex flex-col gap-3">
                                @php
                                    $topics = [
                                        ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'text' => 'Your park accessibility experiences'],
                                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Disney trip planning questions'],
                                        ['icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z', 'text' => 'Podcast guest ideas'],
                                        ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'text' => 'Collaboration & partnerships'],
                                    ];
                                @endphp
                                @foreach ($topics as $topic)
                                    <div class="flex items-center gap-[0.65rem]">
                                        <svg class="text-gold/60 size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $topic['icon'] }}" /></svg>
                                        <span class="text-cream/55 text-base sm:text-sm">{{ $topic['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Quick links --}}
                        <div class="flex flex-col gap-2">
                            <a
                                href="{{ route('blog.index') }}"
                                class="border-cream/6 bg-cream/3 hover:border-gold/15 hover:bg-cream/6 flex min-h-12 items-center justify-between rounded-xl border px-4 py-3 transition-colors"
                            >
                                <span class="text-gold-light text-base font-semibold sm:text-sm">Read the Blog</span>
                                <svg class="text-cream/25 size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                            <a
                                href="{{ route('episodes.index') }}"
                                class="border-cream/6 bg-cream/3 hover:border-gold/15 hover:bg-cream/6 flex min-h-12 items-center justify-between rounded-xl border px-4 py-3 transition-colors"
                            >
                                <span class="text-gold-light text-base font-semibold sm:text-sm">Listen to the Podcast</span>
                                <svg class="text-cream/25 size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
