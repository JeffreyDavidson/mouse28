@extends('layouts.app')

@section('title', 'Contact — Mouse28')

@section('content')
    @if(session('success'))
        {{-- Full-page thank you with animated checkmark --}}
        <section class="min-h-[70vh] flex items-center justify-center bg-gradient-to-br from-cream via-white to-cream relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-1/4 left-1/4 text-6xl sparkle">✨</div>
                <div class="absolute bottom-1/3 right-1/4 text-4xl sparkle-delay">⭐</div>
            </div>
            <div class="text-center px-4 relative z-10">
                {{-- Animated checkmark --}}
                <div class="mx-auto mb-8 w-24 h-24 relative">
                    <svg class="w-24 h-24" viewBox="0 0 96 96">
                        <circle cx="48" cy="48" r="44" fill="none" stroke="#5b3e9e" stroke-width="3" opacity="0.15"/>
                        <circle cx="48" cy="48" r="44" fill="none" stroke="#5b3e9e" stroke-width="3"
                            stroke-dasharray="276.46" stroke-dashoffset="276.46" stroke-linecap="round"
                            style="animation: checkCircle 0.6s ease forwards 0.2s"/>
                        <path d="M30 50 l12 12 l24 -28" fill="none" stroke="#d4a843" stroke-width="4"
                            stroke-linecap="round" stroke-linejoin="round"
                            stroke-dasharray="80" stroke-dashoffset="80"
                            style="animation: checkMark 0.4s ease forwards 0.7s"/>
                    </svg>
                </div>
                <h1 class="font-heading text-4xl md:text-5xl font-bold text-navy mb-4">Message Sent!</h1>
                <p class="text-navy/60 text-lg max-w-md mx-auto mb-2">Thank you for reaching out. We'll get back to you within 48 hours.</p>
                <p class="text-navy/40 text-sm mb-8">In the meantime, feel free to explore our blog and podcast.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/guides" class="inline-flex items-center gap-2 bg-purple hover:bg-purple-dark text-white font-semibold px-6 py-3 rounded-full transition-all hover:shadow-lg hover:-translate-y-0.5">
                        📖 Browse Guides
                    </a>
                    <a href="/blog" class="inline-flex items-center gap-2 text-navy/60 hover:text-purple font-medium transition-colors">
                        Read our blog →
                    </a>
                </div>
            </div>
        </section>
        <style>
            @keyframes checkCircle { to { stroke-dashoffset: 0; } }
            @keyframes checkMark { to { stroke-dashoffset: 0; } }
        </style>
    @else
        {{-- Hero --}}
        <section class="bg-gradient-to-br from-navy via-navy-light to-purple py-16 md:py-24 relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><g fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;><g fill=&quot;white&quot; fill-opacity=&quot;1&quot;><path d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/></g></g></svg>');"></div>
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 right-16 text-4xl sparkle">✨</div>
                <div class="absolute bottom-12 left-20 text-3xl sparkle-delay-2">⭐</div>
            </div>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center relative z-10">
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Get in Touch</span>
                <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-4">We'd Love to Hear From You</h1>
                <p class="text-white/60 mt-6 max-w-xl mx-auto text-lg leading-relaxed">Have a question, story to share, or want to collaborate? Drop us a line. We read every message.</p>
            </div>
        </section>

        {{-- Main Content --}}
        <section class="py-16 md:py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fef9ef 0%, #fdf6e8 50%, #fef9ef 100%);">
            {{-- Subtle decorative dots --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-purple/[0.02] rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-gold/[0.03] rounded-full translate-y-1/2 -translate-x-1/2"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
                <div class="flex flex-col-reverse lg:flex-row gap-10 lg:gap-14">
                    {{-- Form (60%) --}}
                    <div class="lg:w-[58%]">
                        <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5">
                            <h2 class="font-heading text-2xl font-bold text-navy mb-6">Send a Message</h2>

                            <form action="/contact" method="POST" class="space-y-6">
                                @csrf

                                <div class="grid sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="name" class="block text-sm font-semibold text-navy mb-1.5">Name <span class="text-red-400">*</span></label>
                                        <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('name') border-red-300 ring-2 ring-red-100 bg-red-50/50 @enderror"
                                            placeholder="Your name">
                                        @error('name') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-semibold text-navy mb-1.5">Email <span class="text-red-400">*</span></label>
                                        <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('email') border-red-300 ring-2 ring-red-100 bg-red-50/50 @enderror"
                                            placeholder="you@example.com">
                                        @error('email') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-semibold text-navy mb-1.5">Subject <span class="text-red-400">*</span></label>
                                    <select id="subject" name="subject" required
                                        class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('subject') border-red-300 ring-2 ring-red-100 @enderror">
                                        <option value="">Choose a topic...</option>
                                        <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Question</option>
                                        <option value="accessibility" {{ old('subject') == 'accessibility' ? 'selected' : '' }}>Park Accessibility Question</option>
                                        <option value="collaboration" {{ old('subject') == 'collaboration' ? 'selected' : '' }}>Collaboration / Sponsorship</option>
                                        <option value="guest" {{ old('subject') == 'guest' ? 'selected' : '' }}>Guest on the Podcast</option>
                                        <option value="story" {{ old('subject') == 'story' ? 'selected' : '' }}>Share Your Story</option>
                                        <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('subject') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-semibold text-navy mb-1.5">Message <span class="text-red-400">*</span></label>
                                    <textarea id="message" name="message" required rows="6"
                                        class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y @error('message') border-red-300 ring-2 ring-red-100 bg-red-50/50 @enderror"
                                        placeholder="What's on your mind?">{{ old('message') }}</textarea>
                                    @error('message') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                </div>

                                <button type="submit"
                                    class="w-full bg-purple hover:bg-purple-dark text-white font-semibold py-3.5 px-6 rounded-full transition-all hover:shadow-lg hover:shadow-purple/25 hover:-translate-y-0.5 text-lg">
                                    Send Message ✨
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Info Panel (40%) --}}
                    <div class="lg:w-[42%] space-y-6">
                        {{-- Get in Touch card --}}
                        <div class="bg-gradient-to-br from-navy to-navy-light rounded-2xl p-8 text-white relative overflow-hidden">
                            <div class="absolute top-4 right-4 text-3xl opacity-10 sparkle">✨</div>
                            <div class="absolute bottom-6 left-6 text-2xl opacity-10 sparkle-delay">⭐</div>

                            <h2 class="font-heading text-2xl font-bold mb-4">Get in Touch</h2>
                            <p class="text-white/60 text-sm leading-relaxed mb-6">We're a real family sharing real tips. Whether you have a question about DAS, want to share your story, or just say hi, we're here.</p>

                            {{-- Email --}}
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <a href="mailto:hello@mouse28.com" class="text-white font-medium hover:text-gold transition-colors">hello@mouse28.com</a>
                                    <p class="text-white/40 text-xs">We read every message</p>
                                </div>
                            </div>

                            {{-- Response time --}}
                            <div class="flex items-center gap-2 bg-white/10 rounded-xl px-4 py-3 mb-6">
                                <svg class="w-4 h-4 text-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-white/70 text-sm">We typically respond within <strong class="text-white">48 hours</strong></p>
                            </div>

                            {{-- Social Links --}}
                            <p class="text-white/40 text-xs font-semibold uppercase tracking-wider mb-3">Follow Along</p>
                            <div class="flex items-center gap-3">
                                <a href="#" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-colors" aria-label="Instagram">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-colors" aria-label="TikTok">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.73a8.19 8.19 0 004.76 1.52v-3.4a4.85 4.85 0 01-1-.16z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-colors" aria-label="Facebook">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Quick FAQ --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <h3 class="font-heading font-bold text-navy mb-4">Looking for something specific?</h3>
                            <div class="space-y-3">
                                <a href="/guides" class="flex items-center gap-3 p-3 -mx-1 rounded-xl hover:bg-cream transition-colors group">
                                    <span class="text-xl">📖</span>
                                    <div>
                                        <p class="text-sm font-semibold text-navy group-hover:text-purple transition-colors">DAS tips & accessibility guides</p>
                                        <p class="text-xs text-navy/40">Browse our full guide collection</p>
                                    </div>
                                    <svg class="w-4 h-4 text-navy/20 group-hover:text-purple ml-auto flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="/episodes" class="flex items-center gap-3 p-3 -mx-1 rounded-xl hover:bg-cream transition-colors group">
                                    <span class="text-xl">🎙️</span>
                                    <div>
                                        <p class="text-sm font-semibold text-navy group-hover:text-purple transition-colors">Listen to the podcast</p>
                                        <p class="text-xs text-navy/40">New episodes weekly</p>
                                    </div>
                                    <svg class="w-4 h-4 text-navy/20 group-hover:text-purple ml-auto flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
