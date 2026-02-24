@extends('layouts.app')

@section('title', 'Contact — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Get in Touch</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Contact Us</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Have a question, story to share, or want to collaborate? We'd love to hear from you.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl p-6 mb-8 text-center">
                    <span class="text-2xl block mb-2">✨</span>
                    <p class="font-semibold">Message sent!</p>
                    <p class="text-sm mt-1 text-green-600">We'll get back to you as soon as we can.</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5">
                <form action="/contact" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-navy mb-2">Name</label>
                        <input type="text" id="name" name="name" required value="{{ old('name') }}"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                            placeholder="Your name">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-navy mb-2">Email</label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                            placeholder="you@example.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-navy mb-2">Subject</label>
                        <select id="subject" name="subject" required
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all">
                            <option value="">Choose a topic...</option>
                            <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Question</option>
                            <option value="accessibility" {{ old('subject') == 'accessibility' ? 'selected' : '' }}>Park Accessibility Question</option>
                            <option value="collaboration" {{ old('subject') == 'collaboration' ? 'selected' : '' }}>Collaboration / Sponsorship</option>
                            <option value="guest" {{ old('subject') == 'guest' ? 'selected' : '' }}>Guest on the Podcast</option>
                            <option value="story" {{ old('subject') == 'story' ? 'selected' : '' }}>Share Your Story</option>
                            <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-navy mb-2">Message</label>
                        <textarea id="message" name="message" required rows="6"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y"
                            placeholder="What's on your mind?">{{ old('message') }}</textarea>
                        @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-purple hover:bg-purple-dark text-white font-semibold py-3 px-6 rounded-full transition-all hover:shadow-lg hover:shadow-purple/25 hover:-translate-y-0.5">
                        Send Message
                    </button>
                </form>
            </div>

            {{-- Alternative Contact --}}
            <div class="mt-10 text-center">
                <p class="text-navy/50 text-sm mb-4">You can also find us on social media</p>
                <div class="flex items-center justify-center gap-6">
                    <a href="#" class="text-navy/40 hover:text-purple transition-colors" aria-label="Instagram">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="text-navy/40 hover:text-purple transition-colors" aria-label="TikTok">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.73a8.19 8.19 0 004.76 1.52v-3.4a4.85 4.85 0 01-1-.16z"/></svg>
                    </a>
                    <a href="#" class="text-navy/40 hover:text-purple transition-colors" aria-label="Facebook">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
