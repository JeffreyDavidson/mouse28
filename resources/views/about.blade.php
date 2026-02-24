@extends('layouts.app')

@section('title', 'About — Mouse28')
@section('meta_description', 'Meet the Davidsons — Jeffrey, Cassie, and Viola. Learn how our family navigates Disney parks with autism and why we started Mouse28.')

@section('content')
    {{-- Hero with sparkles --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden py-20 md:py-28">
        {{-- Sparkles --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="sparkle absolute top-12 left-[15%] text-gold/60 text-lg">✦</div>
            <div class="sparkle-delay absolute top-20 right-[20%] text-gold/40 text-sm">✦</div>
            <div class="sparkle-delay-2 absolute bottom-16 left-[30%] text-gold/50 text-xl">✦</div>
            <div class="sparkle absolute bottom-20 right-[10%] text-gold/30 text-base">✦</div>
            <div class="sparkle-delay absolute top-1/2 left-[8%] text-purple-light/40 text-sm">✦</div>
            <div class="sparkle-delay-2 absolute top-16 right-[40%] text-gold/40">✦</div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <span class="inline-block text-gold text-sm font-semibold tracking-widest uppercase mb-4 bg-gold/10 px-4 py-1.5 rounded-full">About Us</span>
            <h1 class="font-heading text-4xl md:text-6xl font-bold text-white mt-2">Our Story</h1>
            <p class="text-white/60 mt-5 max-w-xl mx-auto text-lg leading-relaxed">How a family, a little girl, and a whole lot of Disney magic became something worth sharing.</p>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section class="bg-navy-light border-t border-white/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-heading font-bold text-gold">150+</div>
                    <div class="text-white/50 text-sm mt-1.5">Weekly Visits</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-heading font-bold text-gold">4</div>
                    <div class="text-white/50 text-sm mt-1.5">Parks</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-heading font-bold text-gold">1</div>
                    <div class="text-white/50 text-sm mt-1.5">Amazing Daughter</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-heading font-bold text-gold">∞</div>
                    <div class="text-white/50 text-sm mt-1.5">Memories</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Family Photo Placeholder --}}
    <section class="py-16 bg-cream">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="bg-gradient-to-br from-purple/10 to-gold/10 rounded-3xl aspect-video flex items-center justify-center shadow-sm">
                <div class="text-center">
                    <span class="text-7xl block mb-3">👨‍👩‍👧</span>
                    <span class="text-navy/30 text-sm">The Davidson Family — photo coming soon</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Timeline Story --}}
    <section class="py-16 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            {{-- Section: Meet the Davidsons --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter One</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Meet the Davidsons</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>We're Jeffrey and Cassie, and we live in Central Florida with our daughter Viola, who's 8 years old. We're the kind of family that goes to Disney World every single week — not because we're obsessed (okay, maybe a little), but because it's genuinely become one of the best things for our family.</p>
                    <p>When you live 30 minutes from the most magical place on Earth, you'd be surprised how quickly it becomes your go-to spot for Saturday afternoons, birthday celebrations, and even "we just need to get out of the house" moments.</p>
                </div>
            </div>

            {{-- Pull Quote --}}
            <div class="border-l-[5px] border-gold bg-cream-dark rounded-r-2xl p-6 md:p-8 mb-16 ml-8 md:ml-12">
                <p class="text-lg md:text-xl text-navy/80 italic font-heading leading-relaxed">"Viola experiences the parks in ways we never would have noticed on our own. She's taught us that slowing down and paying attention is where the real magic lives."</p>
            </div>

            {{-- Section: Viola's World --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter Two</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Viola's World</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>Viola was diagnosed with autism when she was 3. Like a lot of parents, we went through a period of figuring things out — what works, what doesn't, what environments help her thrive, and which ones overwhelm her.</p>
                    <p>Disney turned out to be one of those places that just <em>works</em> for Viola. Not always perfectly — we've had our share of meltdowns in Tomorrowland and sensory overload in the gift shops. But Disney's accessibility programs, especially the DAS (Disability Access Service) pass, have been a game-changer for our family.</p>
                    <p>More than that, Viola experiences the parks in ways we never would have noticed on our own. She'll spend 20 minutes watching the water patterns in a fountain. She knows every character meet-and-greet by heart. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                </div>
            </div>

            {{-- Section: Why Mouse28 --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter Three</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Why Mouse'28?</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>The name comes from 1928 — the year Mickey Mouse made his debut in <em>Steamboat Willie</em>. It felt right: the beginning of Disney magic, just like Viola is the beginning of ours.</p>
                    <p>We started Mouse'28 because we kept getting the same questions from other autism families: <em>"How do you do Disney with your kid?" "Is the DAS pass worth it?" "What rides are sensory-friendly?"</em></p>
                    <p>We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently. Tips about quiet spots when things get overwhelming. Strategies for wait times. Which characters are the most patient. The best times to visit specific areas.</p>
                    <p>So we hit record. And it turns out there are a lot of families out there who needed to hear exactly this.</p>
                </div>
            </div>

            {{-- Pull Quote --}}
            <div class="border-l-[5px] border-gold bg-cream-dark rounded-r-2xl p-6 md:p-8 mb-16 ml-8 md:ml-12">
                <p class="text-lg md:text-xl text-navy/80 italic font-heading leading-relaxed">"We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently."</p>
            </div>
        </div>
    </section>

    {{-- What We Cover --}}
    <section class="py-16 md:py-24 bg-cream-dark">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">What We Cover</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Everything Disney, Through Our Lens</h2>
            </div>
            <div class="grid sm:grid-cols-2 gap-6">
                {{-- DAS Pass Tips --}}
                <div class="bg-white rounded-2xl p-7 shadow-sm border border-navy/5 hover:shadow-md hover:border-gold/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center mb-4 group-hover:from-purple/20 group-hover:to-gold/20 transition-all">
                        <svg class="w-6 h-6 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-navy text-lg mb-2">DAS Pass Tips</h3>
                    <p class="text-navy/60 text-sm leading-relaxed">How to apply, what to expect, and making the most of disability accommodations at Disney parks.</p>
                </div>

                {{-- Sensory-Friendly Guide --}}
                <div class="bg-white rounded-2xl p-7 shadow-sm border border-navy/5 hover:shadow-md hover:border-gold/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center mb-4 group-hover:from-purple/20 group-hover:to-gold/20 transition-all">
                        <svg class="w-6 h-6 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-navy text-lg mb-2">Sensory-Friendly Guide</h3>
                    <p class="text-navy/60 text-sm leading-relaxed">Quiet spaces, low-stimulation zones, and rides rated by sensory intensity for neurodiverse visitors.</p>
                </div>

                {{-- Park Accessibility --}}
                <div class="bg-white rounded-2xl p-7 shadow-sm border border-navy/5 hover:shadow-md hover:border-gold/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center mb-4 group-hover:from-purple/20 group-hover:to-gold/20 transition-all">
                        <svg class="w-6 h-6 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-navy text-lg mb-2">Park Accessibility</h3>
                    <p class="text-navy/60 text-sm leading-relaxed">Real-world reviews of how accessible Disney parks actually are for diverse needs and abilities.</p>
                </div>

                {{-- Family Disney Life --}}
                <div class="bg-white rounded-2xl p-7 shadow-sm border border-navy/5 hover:shadow-md hover:border-gold/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center mb-4 group-hover:from-purple/20 group-hover:to-gold/20 transition-all">
                        <svg class="w-6 h-6 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" /></svg>
                    </div>
                    <h3 class="font-heading font-semibold text-navy text-lg mb-2">Family Disney Life</h3>
                    <p class="text-navy/60 text-sm leading-relaxed">The funny, messy, magical reality of weekly Disney trips with our family.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Your Hosts --}}
    <section class="py-16 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Your Hosts</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">The Voices Behind Mouse'28</h2>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                {{-- Jeffrey --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-navy/5 text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-purple/20 to-gold/20 mx-auto mb-5 flex items-center justify-center">
                        <span class="text-5xl">👨</span>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-navy">Jeffrey Davidson</h3>
                    <p class="text-gold text-sm font-medium mt-1 mb-4">Co-Host & Dad Extraordinaire</p>
                    <p class="text-navy/60 text-sm leading-relaxed">The planner, the podcast editor, and the guy who knows every shortcut in Magic Kingdom. Jeffrey brings the strategy and the dad jokes.</p>
                </div>
                {{-- Cassie --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-navy/5 text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-purple/20 to-gold/20 mx-auto mb-5 flex items-center justify-center">
                        <span class="text-5xl">👩</span>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-navy">Cassie Davidson</h3>
                    <p class="text-gold text-sm font-medium mt-1 mb-4">Co-Host & Accessibility Advocate</p>
                    <p class="text-navy/60 text-sm leading-relaxed">The heart of Mouse'28. Cassie champions accessibility awareness and brings warmth, honesty, and a snack bag that never runs out.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden py-20">
        {{-- Sparkles --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="sparkle absolute top-8 left-[12%] text-gold/50 text-lg">✦</div>
            <div class="sparkle-delay absolute top-16 right-[18%] text-gold/30 text-sm">✦</div>
            <div class="sparkle-delay-2 absolute bottom-12 left-[25%] text-gold/40 text-xl">✦</div>
            <div class="sparkle absolute bottom-8 right-[15%] text-gold/50">✦</div>
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white">Ready to Join Our Disney Family?</h2>
            <p class="text-white/60 mt-4 text-lg">Listen to the podcast, read the blog, or just say hi. We'd love to connect.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                <a href="/episodes" class="bg-purple hover:bg-purple-dark text-white font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-purple/25 hover:-translate-y-0.5 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" /></svg>
                    Listen to the Podcast
                </a>
                <a href="/blog" class="bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">✨ Read the Blog</a>
            </div>
        </div>
    </section>
@endsection
