@extends('layouts.app')

@section('title', 'Share Your Story — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Community</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Share Your Story</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Every family's Disney experience is unique. We'd love to hear yours — especially if it can help another family feel less alone.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl p-8 mb-8 text-center">
                    <span class="text-3xl block mb-3">💛</span>
                    <p class="font-heading text-xl font-semibold mb-2">Thank you for sharing!</p>
                    <p class="text-sm text-green-600">We'll review your story and get it published soon. Your experience could help another family.</p>
                    <a href="/stories" class="inline-block mt-4 text-green-700 font-semibold text-sm hover:underline">← Read other stories</a>
                </div>
            @endif

            <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5">
                <div class="mb-8">
                    <h2 class="font-heading text-xl font-bold text-navy mb-2">Tell us about your family's Disney experience</h2>
                    <p class="text-navy/50 text-sm">All fields marked with * are required. Your email won't be published.</p>
                </div>

                <form action="/stories" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-navy mb-2">Your Name *</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                placeholder="First name or nickname">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-navy mb-2">Email *</label>
                            <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                placeholder="Won't be published">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-navy mb-2">Where are you from?</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                            placeholder="City, State (optional)">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="child_name" class="block text-sm font-medium text-navy mb-2">Child's First Name</label>
                            <input type="text" id="child_name" name="child_name" value="{{ old('child_name') }}"
                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                placeholder="Optional — first name only">
                        </div>

                        <div>
                            <label for="child_age" class="block text-sm font-medium text-navy mb-2">Child's Age</label>
                            <input type="number" id="child_age" name="child_age" value="{{ old('child_age') }}" min="0" max="100"
                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                placeholder="Optional">
                        </div>
                    </div>

                    <hr class="border-navy/5">

                    <div>
                        <label for="title" class="block text-sm font-medium text-navy mb-2">Story Title *</label>
                        <input type="text" id="title" name="title" required value="{{ old('title') }}"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                            placeholder="Give your story a title">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="story" class="block text-sm font-medium text-navy mb-2">Your Story *</label>
                        <textarea id="story" name="story" required rows="8"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y"
                            placeholder="Share your family's Disney experience — the magical moments, the challenges, whatever feels right...">{{ old('story') }}</textarea>
                        <p class="text-navy/30 text-xs mt-1">Minimum 50 characters</p>
                        @error('story') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="favorite_park" class="block text-sm font-medium text-navy mb-2">Favorite Disney Park</label>
                        <select id="favorite_park" name="favorite_park"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all">
                            <option value="">Select a park (optional)</option>
                            <option value="Magic Kingdom" {{ old('favorite_park') == 'Magic Kingdom' ? 'selected' : '' }}>Magic Kingdom</option>
                            <option value="EPCOT" {{ old('favorite_park') == 'EPCOT' ? 'selected' : '' }}>EPCOT</option>
                            <option value="Hollywood Studios" {{ old('favorite_park') == 'Hollywood Studios' ? 'selected' : '' }}>Hollywood Studios</option>
                            <option value="Animal Kingdom" {{ old('favorite_park') == 'Animal Kingdom' ? 'selected' : '' }}>Animal Kingdom</option>
                            <option value="All of them!" {{ old('favorite_park') == 'All of them!' ? 'selected' : '' }}>All of them! ✨</option>
                        </select>
                    </div>

                    <div>
                        <label for="favorite_tip" class="block text-sm font-medium text-navy mb-2">Your #1 Tip for Other Families</label>
                        <textarea id="favorite_tip" name="favorite_tip" rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y"
                            placeholder="What's the one thing you wish you knew before your first visit?">{{ old('favorite_tip') }}</textarea>
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="photo_consent" name="photo_consent" value="1"
                            class="mt-1 rounded border-navy/20 text-purple focus:ring-purple/30"
                            {{ old('photo_consent') ? 'checked' : '' }}>
                        <label for="photo_consent" class="text-sm text-navy/60">
                            I'm open to being contacted about sharing a photo with my story (completely optional)
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full bg-purple hover:bg-purple-dark text-white font-semibold py-3.5 px-6 rounded-full transition-all hover:shadow-lg hover:shadow-purple/25 hover:-translate-y-0.5">
                        Share My Story
                    </button>

                    <p class="text-center text-navy/30 text-xs">
                        All stories are reviewed before publishing. We respect your privacy and will never share your email.
                    </p>
                </form>
            </div>
        </div>
    </section>
@endsection
