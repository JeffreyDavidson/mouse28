@extends('layouts.app')

@section('title', 'Contact — Mouse28')

@section('content')
    @if(session('success'))
        <section class="relative flex min-h-[70vh] items-center justify-center overflow-hidden bg-linear-to-br from-cream via-white to-cream">
            <div class="relative z-10 px-4 text-center">
                <div class="relative mx-auto mb-8 size-24">
                    <svg class="size-24" viewBox="0 0 96 96">
                        <circle cx="48" cy="48" r="44" fill="none" stroke="#5b3e9e" stroke-width="3" opacity="0.15"/>
                        <circle cx="48" cy="48" r="44" fill="none" stroke="#5b3e9e" stroke-width="3"
                            stroke-dasharray="276.46" stroke-dashoffset="276.46" stroke-linecap="round"
                            class="contact-check-circle"/>
                        <path d="M30 50 l12 12 l24 -28" fill="none" stroke="#d4a843" stroke-width="4"
                            stroke-linecap="round" stroke-linejoin="round"
                            stroke-dasharray="80" stroke-dashoffset="80"
                            class="contact-check-mark"/>
                    </svg>
                </div>
                <h1 class="mb-4 font-heading text-4xl font-bold text-navy md:text-5xl">Message Sent!</h1>
                <p class="mx-auto mb-2 max-w-md text-lg text-navy/60">Thank you for reaching out. We'll get back to you within 48 hours.</p>
                <p class="mb-8 text-sm text-navy/40">In the meantime, feel free to explore our blog and podcast.</p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="/blog" class="inline-flex items-center gap-2 rounded-full bg-purple px-6 py-3 font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-purple-dark hover:shadow-lg">Read our blog</a>
                    <a href="/episodes" class="inline-flex items-center gap-2 font-medium text-navy/60 transition-colors hover:text-purple">Listen to podcast →</a>
                </div>
            </div>
        </section>
    @else
        {{-- Full-width dark hero with form embedded --}}
        <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-light to-navy">
            {{-- Ambient glows --}}
            <div class="pointer-events-none absolute top-[-20%] right-[-5%] size-[700px] bg-[radial-gradient(circle,rgb(212_168_67/5%)_0%,transparent_60%)]"></div>
            <div class="pointer-events-none absolute bottom-[-30%] left-[-10%] size-[500px] bg-[radial-gradient(circle,rgb(91_62_158/15%)_0%,transparent_60%)]"></div>

            <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 md:py-24">
                {{-- Header --}}
                <div class="mb-14 text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                        <span class="size-2 animate-pulse rounded-full bg-gold"></span>
                        <span class="text-sm font-semibold tracking-widest text-gold uppercase">Get in Touch</span>
                    </div>
                    <h1 class="mt-2 font-heading text-4xl font-bold text-white md:text-5xl lg:text-6xl">We'd Love to Hear From You</h1>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-white/60">Have a question about DAS, want to share your story, or just say hi? We read every message.</p>
                </div>

                {{-- Two-column layout --}}
                <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px]">
                    {{-- Form --}}
                    <div class="rounded-2xl border border-cream/10 bg-cream/3 p-5 backdrop-blur-sm sm:p-8 md:p-10">
                        <form action="/contact" method="POST" class="flex flex-col gap-6">
                            @csrf
                            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                            @if ($errors->has('contact_rate_limit'))
                                <div role="alert" class="rounded-xl border border-red-400/25 bg-red-400/10 px-4 py-3 text-base text-red-200 sm:text-sm">
                                    {{ $errors->first('contact_rate_limit') }}
                                </div>
                            @endif

                            <!-- Honeypot - hidden from humans, bots fill this -->
                            <div class="absolute top-[-9999px] left-[-9999px]" aria-hidden="true">
                                <label for="website_url">Website</label>
                                <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-2 block text-sm font-semibold text-gold-light">Name</label>
                                    <input type="text" id="name" name="name" required autocomplete="name" value="{{ old('name') }}" placeholder="Your name"
                                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                                        class="min-h-12 w-full rounded-xl border border-cream/10 bg-cream/4 px-4 py-3 text-base text-cream transition-colors placeholder:text-cream/30 focus:border-gold/50 focus:bg-cream/6 focus:ring-2 focus:ring-gold/20 focus:outline-none sm:text-sm"
                                    >
                                    @error('name') <p id="name-error" role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-semibold text-gold-light">Email</label>
                                    <input type="email" id="email" name="email" required autocomplete="email" inputmode="email" value="{{ old('email') }}" placeholder="you@example.com"
                                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                                        class="min-h-12 w-full rounded-xl border border-cream/10 bg-cream/4 px-4 py-3 text-base text-cream transition-colors placeholder:text-cream/30 focus:border-gold/50 focus:bg-cream/6 focus:ring-2 focus:ring-gold/20 focus:outline-none sm:text-sm"
                                    >
                                    @error('email') <p id="email-error" role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="mb-2 block text-sm font-semibold text-gold-light">Topic</label>
                                <select id="subject" name="subject" required
                                    @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror
                                    class="contact-select min-h-12 w-full rounded-xl border border-cream/10 bg-cream/4 px-4 py-3 text-base text-cream/70 transition-colors focus:border-gold/50 focus:ring-2 focus:ring-gold/20 focus:outline-none sm:text-sm"
                                >
                                    <option value="">Choose a topic...</option>
                                    <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Question</option>
                                    <option value="accessibility" {{ old('subject') == 'accessibility' ? 'selected' : '' }}>Park Accessibility Question</option>
                                    <option value="collaboration" {{ old('subject') == 'collaboration' ? 'selected' : '' }}>Collaboration / Sponsorship</option>
                                    <option value="guest" {{ old('subject') == 'guest' ? 'selected' : '' }}>Guest on the Podcast</option>
                                    <option value="story" {{ old('subject') == 'story' ? 'selected' : '' }}>Share Your Story</option>
                                    <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('subject') <p id="subject-error" role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="message" class="mb-2 block text-sm font-semibold text-gold-light">Message</label>
                                <textarea id="message" name="message" required rows="5" placeholder="What's on your mind?"
                                    @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                                    class="min-h-36 w-full resize-y rounded-xl border border-cream/10 bg-cream/4 px-4 py-3 text-base text-cream transition-colors placeholder:text-cream/30 focus:border-gold/50 focus:bg-cream/6 focus:ring-2 focus:ring-gold/20 focus:outline-none sm:text-sm"
                                >{{ old('message') }}</textarea>
                                @error('message') <p id="message-error" role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                @if (config('services.turnstile.site_key'))
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="{{ config('services.turnstile.action', 'contact-form') }}" data-theme="dark"></div>
                                @else
                                    <p role="alert" class="text-base text-red-200 sm:text-sm">Contact verification is temporarily unavailable. Please try again later.</p>
                                @endif
                                @error('cf-turnstile-response') <p id="turnstile-error" role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="min-h-12 w-full rounded-xl bg-linear-to-br from-gold to-gold-dark px-6 py-3 text-base font-semibold text-navy transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/30 focus-visible:outline-gold sm:text-sm">
                                Send Message
                            </button>
                        </form>
                    </div>

                    {{-- Sidebar --}}
                    <div class="flex flex-col gap-6">
                        {{-- Email card --}}
                        <div class="rounded-2xl border border-cream/8 bg-cream/4 p-6">
                            <div class="mb-5 flex items-center gap-[0.85rem]">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-[0.6rem] bg-gold/12">
                                    <svg class="size-[18px] text-gold-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <a href="mailto:mouse28podcast@gmail.com" class="text-base font-semibold break-all text-cream sm:text-sm">mouse28podcast@gmail.com</a>
                                    <p class="mt-1 text-sm text-cream/35">We read every message</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-[0.6rem] bg-cream/4 px-[0.85rem] py-[0.65rem]">
                                <svg class="size-3.5 shrink-0 text-gold-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-base text-cream/50 sm:text-sm">Typically within <strong class="text-cream/80">48 hours</strong></p>
                            </div>
                        </div>

                        {{-- Topics we love --}}
                        <div class="rounded-2xl border border-cream/8 bg-cream/4 p-6">
                            <h3 class="mb-4 font-heading text-base font-bold text-cream">We Love Hearing About</h3>
                            <div class="flex flex-col gap-3">
                                @php
                                    $topics = [
                                        ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'text' => 'Your park accessibility experiences'],
                                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Family Disney stories'],
                                        ['icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z', 'text' => 'Podcast guest ideas'],
                                        ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'text' => 'Collaboration & partnerships'],
                                    ];
                                @endphp
                                @foreach($topics as $topic)
                                    <div class="flex items-center gap-[0.65rem]">
                                        <svg class="size-3.5 shrink-0 text-gold/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $topic['icon'] }}"/></svg>
                                        <span class="text-base text-cream/55 sm:text-sm">{{ $topic['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Quick links --}}
                        <div class="flex flex-col gap-2">
                            <a href="/blog" class="flex min-h-12 items-center justify-between rounded-xl border border-cream/6 bg-cream/3 px-4 py-3 transition-all hover:border-gold/15 hover:bg-cream/6">
                                <span class="text-base font-semibold text-gold-light sm:text-sm">Read the Blog</span>
                                <svg class="size-3.5 text-cream/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <a href="/episodes" class="flex min-h-12 items-center justify-between rounded-xl border border-cream/6 bg-cream/3 px-4 py-3 transition-all hover:border-gold/15 hover:bg-cream/6">
                                <span class="text-base font-semibold text-gold-light sm:text-sm">Listen to the Podcast</span>
                                <svg class="size-3.5 text-cream/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
