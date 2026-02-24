@extends('layouts.app')

@section('title', 'Share Your Story — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Community</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Share Your Story</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto text-lg">Every family's Disney experience is unique. We'd love to hear yours — especially if it can help another family feel less alone.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            @if(session('success'))
                <div class="max-w-2xl mx-auto bg-green-50 border border-green-200 text-green-800 rounded-2xl p-8 mb-8 text-center">
                    <span class="text-3xl block mb-3">💛</span>
                    <p class="font-heading text-xl font-semibold mb-2">Thank you for sharing!</p>
                    <p class="text-sm text-green-600">We'll review your story and get it published soon. Your experience could help another family.</p>
                    <a href="/stories" class="inline-block mt-4 text-green-700 font-semibold text-sm hover:underline">← Read other stories</a>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Form --}}
                <div class="lg:w-[65%]">
                    <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5">
                        {{-- Progress Indicator --}}
                        <div class="flex items-center justify-between mb-10 px-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                                <span class="text-sm font-medium text-navy hidden sm:inline">About You</span>
                            </div>
                            <div class="flex-1 h-px bg-navy/10 mx-3"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple/20 text-purple rounded-full flex items-center justify-center text-sm font-bold">2</div>
                                <span class="text-sm font-medium text-navy/50 hidden sm:inline">Your Story</span>
                            </div>
                            <div class="flex-1 h-px bg-navy/10 mx-3"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple/20 text-purple rounded-full flex items-center justify-center text-sm font-bold">3</div>
                                <span class="text-sm font-medium text-navy/50 hidden sm:inline">Your Tips</span>
                            </div>
                        </div>

                        <form action="/stories" method="POST" class="space-y-8" id="story-form">
                            @csrf

                            {{-- Section 1: About You --}}
                            <fieldset>
                                <legend class="font-heading text-xl font-bold text-navy mb-1 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-purple text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                    About You
                                </legend>
                                <p class="text-navy/40 text-sm mb-6">Tell us a little about your family.</p>

                                <div class="space-y-5">
                                    <div class="grid sm:grid-cols-2 gap-5">
                                        <div class="relative">
                                            <label for="name" class="block text-sm font-semibold text-navy mb-1.5">Your Name <span class="text-red-400">*</span></label>
                                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('name') border-red-300 ring-2 ring-red-100 @enderror"
                                                placeholder="First name or nickname">
                                            @error('name') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-semibold text-navy mb-1.5">Email <span class="text-red-400">*</span></label>
                                            <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('email') border-red-300 ring-2 ring-red-100 @enderror"
                                                placeholder="Won't be published">
                                            @error('email') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="location" class="block text-sm font-semibold text-navy mb-1.5">Where are you from?</label>
                                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                            placeholder="City, State (optional)">
                                    </div>

                                    <div class="grid sm:grid-cols-2 gap-5">
                                        <div>
                                            <label for="child_name" class="block text-sm font-semibold text-navy mb-1.5">Child's First Name</label>
                                            <input type="text" id="child_name" name="child_name" value="{{ old('child_name') }}"
                                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                                placeholder="Optional — first name only">
                                        </div>

                                        <div>
                                            <label for="child_age" class="block text-sm font-semibold text-navy mb-1.5">Child's Age</label>
                                            <input type="number" id="child_age" name="child_age" value="{{ old('child_age') }}" min="0" max="100"
                                                class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all"
                                                placeholder="Optional">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            {{-- Divider --}}
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-navy/5"></div></div>
                                <div class="relative flex justify-center"><span class="bg-white px-4 text-navy/20 text-xs">✨</span></div>
                            </div>

                            {{-- Section 2: Your Story --}}
                            <fieldset>
                                <legend class="font-heading text-xl font-bold text-navy mb-1 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-purple text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                    Your Story
                                </legend>
                                <p class="text-navy/40 text-sm mb-6">Share your family's Disney experience.</p>

                                <div class="space-y-5">
                                    <div>
                                        <label for="title" class="block text-sm font-semibold text-navy mb-1.5">Story Title <span class="text-red-400">*</span></label>
                                        <input type="text" id="title" name="title" required value="{{ old('title') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all @error('title') border-red-300 ring-2 ring-red-100 @enderror"
                                            placeholder="Give your story a title">
                                        @error('title') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                    </div>

                                    <div x-data="{ charCount: {{ Str::length(old('story', '')) }} }">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="story" class="block text-sm font-semibold text-navy">Your Story <span class="text-red-400">*</span></label>
                                            <span class="text-xs" :class="charCount < 50 ? 'text-red-400' : 'text-green-600'" x-text="charCount + ' characters' + (charCount < 50 ? ' (min 50)' : ' ✓')"></span>
                                        </div>
                                        <textarea id="story" name="story" required rows="8"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y @error('story') border-red-300 ring-2 ring-red-100 @enderror"
                                            placeholder="Share your family's Disney experience — the magical moments, the challenges, whatever feels right..."
                                            x-on:input="charCount = $event.target.value.length">{{ old('story') }}</textarea>
                                        <p class="text-navy/30 text-xs mt-1">Minimum 50 characters</p>
                                        @error('story') <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="favorite_park" class="block text-sm font-semibold text-navy mb-1.5">Favorite Disney Park</label>
                                        <select id="favorite_park" name="favorite_park"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all">
                                            <option value="">Select a park (optional)</option>
                                            <option value="Magic Kingdom" {{ old('favorite_park') == 'Magic Kingdom' ? 'selected' : '' }}>🏰 Magic Kingdom</option>
                                            <option value="EPCOT" {{ old('favorite_park') == 'EPCOT' ? 'selected' : '' }}>🌐 EPCOT</option>
                                            <option value="Hollywood Studios" {{ old('favorite_park') == 'Hollywood Studios' ? 'selected' : '' }}>🎬 Hollywood Studios</option>
                                            <option value="Animal Kingdom" {{ old('favorite_park') == 'Animal Kingdom' ? 'selected' : '' }}>🦁 Animal Kingdom</option>
                                            <option value="All of them!" {{ old('favorite_park') == 'All of them!' ? 'selected' : '' }}>✨ All of them!</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            {{-- Divider --}}
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-navy/5"></div></div>
                                <div class="relative flex justify-center"><span class="bg-white px-4 text-navy/20 text-xs">✨</span></div>
                            </div>

                            {{-- Section 3: Your Tips --}}
                            <fieldset>
                                <legend class="font-heading text-xl font-bold text-navy mb-1 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-purple text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                    Your Tips
                                </legend>
                                <p class="text-navy/40 text-sm mb-6">Help other families with what you've learned.</p>

                                <div class="space-y-5">
                                    <div>
                                        <label for="favorite_tip" class="block text-sm font-semibold text-navy mb-1.5">Your #1 Tip for Other Families</label>
                                        <textarea id="favorite_tip" name="favorite_tip" rows="3"
                                            class="w-full px-4 py-3 rounded-xl border border-navy/10 bg-cream/50 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all resize-y"
                                            placeholder="What's the one thing you wish you knew before your first visit?">{{ old('favorite_tip') }}</textarea>
                                    </div>

                                    <div class="flex items-start gap-3 bg-cream/50 rounded-xl p-4 border border-navy/5">
                                        <input type="checkbox" id="photo_consent" name="photo_consent" value="1"
                                            class="mt-1 rounded border-navy/20 text-purple focus:ring-purple/30"
                                            {{ old('photo_consent') ? 'checked' : '' }}>
                                        <label for="photo_consent" class="text-sm text-navy/60">
                                            I'm open to being contacted about sharing a photo with my story (completely optional)
                                        </label>
                                    </div>
                                </div>
                            </fieldset>

                            <button type="submit"
                                class="w-full bg-purple hover:bg-purple-dark text-white font-semibold py-4 px-6 rounded-full transition-all hover:shadow-lg hover:shadow-purple/25 hover:-translate-y-0.5 text-lg">
                                Share My Story 💛
                            </button>

                            <p class="text-center text-navy/30 text-xs">
                                All stories are reviewed before publishing. We respect your privacy and will never share your email.
                            </p>
                        </form>
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[35%] space-y-6">
                    {{-- Testimonial quotes --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                        <h3 class="font-heading text-lg font-bold text-navy mb-4">From Our Community</h3>

                        <div class="space-y-6">
                            <div class="relative">
                                <span class="font-heading text-4xl text-purple/15 leading-none absolute -top-1 -left-1">"</span>
                                <p class="text-navy/60 text-sm leading-relaxed pl-5 italic">Sharing our story helped us connect with so many other families. We realized we weren't alone in this journey.</p>
                                <p class="text-navy font-semibold text-xs mt-2 pl-5">— Sarah, mom to Jake (7)</p>
                            </div>

                            <div class="h-px bg-navy/5"></div>

                            <div class="relative">
                                <span class="font-heading text-4xl text-purple/15 leading-none absolute -top-1 -left-1">"</span>
                                <p class="text-navy/60 text-sm leading-relaxed pl-5 italic">Reading other families' experiences gave us the confidence to plan our first Disney trip. It was magical.</p>
                                <p class="text-navy font-semibold text-xs mt-2 pl-5">— Marcus, dad to Lily (5)</p>
                            </div>
                        </div>
                    </div>

                    {{-- What to expect --}}
                    <div class="bg-gradient-to-br from-purple/5 to-gold/5 rounded-2xl p-6 border border-purple/10">
                        <h3 class="font-heading text-lg font-bold text-navy mb-3">What Happens Next?</h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-purple/10 text-purple rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                                <p class="text-navy/60 text-sm">We'll review your story within 48 hours</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-purple/10 text-purple rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                                <p class="text-navy/60 text-sm">Your story gets published on our community page</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-purple/10 text-purple rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                                <p class="text-navy/60 text-sm">Other families find hope and connection in your words 💛</p>
                            </div>
                        </div>
                    </div>

                    {{-- Privacy note --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                        <h3 class="font-heading text-lg font-bold text-navy mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Your Privacy
                        </h3>
                        <p class="text-navy/50 text-sm leading-relaxed">Your email is never published or shared. You control what details are included in your story.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
