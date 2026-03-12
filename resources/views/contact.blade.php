@extends('layouts.app')

@section('title', 'Contact — Mouse28')

@section('content')
    @if(session('success'))
        <section class="min-h-[70vh] flex items-center justify-center bg-gradient-to-br from-cream via-white to-cream relative overflow-hidden">
            <div class="text-center px-4 relative z-10">
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
                <div class="flex items-center justify-center gap-4">
                    <a href="/blog" class="inline-flex items-center gap-2 bg-purple hover:bg-purple-dark text-white font-semibold px-6 py-3 rounded-full transition-all hover:shadow-lg hover:-translate-y-0.5">Read our blog</a>
                    <a href="/episodes" class="inline-flex items-center gap-2 text-navy/60 hover:text-purple font-medium transition-colors">Listen to podcast →</a>
                </div>
            </div>
        </section>
        <style>
            @keyframes checkCircle { to { stroke-dashoffset: 0; } }
            @keyframes checkMark { to { stroke-dashoffset: 0; } }
        </style>
    @else
        {{-- Full-width dark hero with form embedded --}}
        <section class="bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
            {{-- Ambient glows --}}
            <div style="position: absolute; top: -20%; right: -5%; width: 700px; height: 700px; background: radial-gradient(circle, rgba(212, 168, 67, 0.05) 0%, transparent 60%); pointer-events: none;"></div>
            <div style="position: absolute; bottom: -30%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(91, 62, 158, 0.15) 0%, transparent 60%); pointer-events: none;"></div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-16 md:py-24">
                {{-- Header --}}
                <div class="text-center mb-14">
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                        <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                        <span class="text-gold text-sm font-semibold tracking-widest uppercase">Get in Touch</span>
                    </div>
                    <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-2">We'd Love to Hear From You</h1>
                    <p class="text-white/60 mt-4 max-w-xl mx-auto text-lg">Have a question about DAS, want to share your story, or just say hi? We read every message.</p>
                </div>

                {{-- Two-column layout --}}
                <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;" class="lg:!grid-cols-[1fr_340px]">
                    {{-- Form --}}
                    <div style="background: rgba(254, 249, 239, 0.03); border: 1px solid rgba(254, 249, 239, 0.08); border-radius: 1.5rem; padding: 2.5rem; backdrop-filter: blur(10px);">
                        <form action="/contact" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                            @csrf
                            <!-- Honeypot - hidden from humans, bots fill this -->
                            <div style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                                <label for="website_url">Website</label>
                                <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <div>
                                    <label for="name" style="display: block; font-size: 0.75rem; font-weight: 600; color: #f0c75e; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;">Name</label>
                                    <input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="Your name"
                                        style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.04); color: #fef9ef; font-size: 0.9rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.2s; box-sizing: border-box;"
                                        onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(254,249,239,0.06)'"
                                        onblur="this.style.borderColor='rgba(254,249,239,0.1)';this.style.background='rgba(254,249,239,0.04)'"
                                    >
                                    @error('name') <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.4rem;">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" style="display: block; font-size: 0.75rem; font-weight: 600; color: #f0c75e; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;">Email</label>
                                    <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com"
                                        style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.04); color: #fef9ef; font-size: 0.9rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.2s; box-sizing: border-box;"
                                        onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(254,249,239,0.06)'"
                                        onblur="this.style.borderColor='rgba(254,249,239,0.1)';this.style.background='rgba(254,249,239,0.04)'"
                                    >
                                    @error('email') <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.4rem;">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="subject" style="display: block; font-size: 0.75rem; font-weight: 600; color: #f0c75e; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;">Topic</label>
                                <select id="subject" name="subject" required
                                    style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.04); color: rgba(254,249,239,0.7); font-size: 0.9rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.2s; box-sizing: border-box; -webkit-appearance: none; appearance: none; background-image: url('data:image/svg+xml,<svg width=&quot;12&quot; height=&quot;8&quot; viewBox=&quot;0 0 12 8&quot; fill=&quot;none&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><path d=&quot;M1 1.5L6 6.5L11 1.5&quot; stroke=&quot;rgba(254,249,239,0.4)&quot; stroke-width=&quot;1.5&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot;/></svg>'); background-repeat: no-repeat; background-position: right 1rem center;"
                                    onfocus="this.style.borderColor='rgba(212,168,67,0.4)'"
                                    onblur="this.style.borderColor='rgba(254,249,239,0.1)'"
                                >
                                    <option value="" style="background: #1a1040;">Choose a topic...</option>
                                    <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }} style="background: #1a1040;">General Question</option>
                                    <option value="accessibility" {{ old('subject') == 'accessibility' ? 'selected' : '' }} style="background: #1a1040;">Park Accessibility Question</option>
                                    <option value="collaboration" {{ old('subject') == 'collaboration' ? 'selected' : '' }} style="background: #1a1040;">Collaboration / Sponsorship</option>
                                    <option value="guest" {{ old('subject') == 'guest' ? 'selected' : '' }} style="background: #1a1040;">Guest on the Podcast</option>
                                    <option value="story" {{ old('subject') == 'story' ? 'selected' : '' }} style="background: #1a1040;">Share Your Story</option>
                                    <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }} style="background: #1a1040;">Other</option>
                                </select>
                                @error('subject') <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.4rem;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="message" style="display: block; font-size: 0.75rem; font-weight: 600; color: #f0c75e; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif; text-transform: uppercase; letter-spacing: 0.05em;">Message</label>
                                <textarea id="message" name="message" required rows="5" placeholder="What's on your mind?"
                                    style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.04); color: #fef9ef; font-size: 0.9rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.2s; resize: vertical; box-sizing: border-box; min-height: 140px;"
                                    onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(254,249,239,0.06)'"
                                    onblur="this.style.borderColor='rgba(254,249,239,0.1)';this.style.background='rgba(254,249,239,0.04)'"
                                >{{ old('message') }}</textarea>
                                @error('message') <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.4rem;">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" style="
                                width: 100%; padding: 0.85rem 1.5rem; border-radius: 0.75rem; border: none; cursor: pointer;
                                background: linear-gradient(135deg, #d4a843, #b8922e); color: #1a1040;
                                font-size: 0.9rem; font-weight: 700; font-family: 'Poppins', sans-serif;
                                transition: all 0.2s; letter-spacing: 0.02em;
                            " onmouseenter="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 25px rgba(212,168,67,0.3)'" onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                                Send Message
                            </button>
                        </form>
                    </div>

                    {{-- Sidebar --}}
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        {{-- Email card --}}
                        <div style="background: rgba(254,249,239,0.04); border: 1px solid rgba(254,249,239,0.08); border-radius: 1rem; padding: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem;">
                                <div style="width: 40px; height: 40px; border-radius: 0.6rem; background: rgba(212,168,67,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 18px; height: 18px; color: #f0c75e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <a href="mailto:hello@mouse28.com" style="color: #fef9ef; font-weight: 600; font-size: 0.9rem; font-family: 'Poppins', sans-serif; text-decoration: none;">hello@mouse28.com</a>
                                    <p style="color: rgba(254,249,239,0.35); font-size: 0.75rem; font-family: 'Poppins', sans-serif;">We read every message</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(254,249,239,0.04); border-radius: 0.6rem; padding: 0.65rem 0.85rem;">
                                <svg style="width: 14px; height: 14px; color: #f0c75e; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p style="color: rgba(254,249,239,0.5); font-size: 0.8rem; font-family: 'Poppins', sans-serif;">Typically within <strong style="color: rgba(254,249,239,0.8);">48 hours</strong></p>
                            </div>
                        </div>

                        {{-- Topics we love --}}
                        <div style="background: rgba(254,249,239,0.04); border: 1px solid rgba(254,249,239,0.08); border-radius: 1rem; padding: 1.5rem;">
                            <h3 class="font-heading" style="font-size: 1rem; font-weight: 700; color: #fef9ef; margin-bottom: 1rem;">We Love Hearing About</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @php
                                    $topics = [
                                        ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'text' => 'Your park accessibility experiences'],
                                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Family Disney stories'],
                                        ['icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z', 'text' => 'Podcast guest ideas'],
                                        ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'text' => 'Collaboration & partnerships'],
                                    ];
                                @endphp
                                @foreach($topics as $topic)
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <svg style="width: 14px; height: 14px; color: rgba(212,168,67,0.6); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $topic['icon'] }}"/></svg>
                                        <span style="color: rgba(254,249,239,0.55); font-size: 0.8rem; font-family: 'Poppins', sans-serif;">{{ $topic['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Quick links --}}
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="/blog" style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border-radius: 0.75rem; background: rgba(254,249,239,0.03); border: 1px solid rgba(254,249,239,0.06); text-decoration: none; transition: all 0.2s;" onmouseenter="this.style.background='rgba(254,249,239,0.06)';this.style.borderColor='rgba(212,168,67,0.15)'" onmouseleave="this.style.background='rgba(254,249,239,0.03)';this.style.borderColor='rgba(254,249,239,0.06)'">
                                <span style="color: #f0c75e; font-size: 0.8rem; font-weight: 600; font-family: 'Poppins', sans-serif;">Read the Blog</span>
                                <svg style="width: 14px; height: 14px; color: rgba(254,249,239,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <a href="/episodes" style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border-radius: 0.75rem; background: rgba(254,249,239,0.03); border: 1px solid rgba(254,249,239,0.06); text-decoration: none; transition: all 0.2s;" onmouseenter="this.style.background='rgba(254,249,239,0.06)';this.style.borderColor='rgba(212,168,67,0.15)'" onmouseleave="this.style.background='rgba(254,249,239,0.03)';this.style.borderColor='rgba(254,249,239,0.06)'">
                                <span style="color: #f0c75e; font-size: 0.8rem; font-weight: 600; font-family: 'Poppins', sans-serif;">Listen to the Podcast</span>
                                <svg style="width: 14px; height: 14px; color: rgba(254,249,239,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <style>
        @media (min-width: 1024px) {
            .lg\:\!grid-cols-\[1fr_340px\] { grid-template-columns: 1fr 340px !important; }
        }
    </style>
@endsection
