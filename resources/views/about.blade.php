@extends('layouts.app')

@section('title', 'About — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">About Us</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Our Story</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">How a family, a little girl, and a whole lot of Disney magic became a podcast.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            {{-- Family Photo --}}
            <div class="bg-gradient-to-br from-purple/10 to-gold/10 rounded-2xl aspect-video flex items-center justify-center mb-12">
                <div class="text-center">
                    <span class="text-7xl block mb-3">👨‍👩‍👧</span>
                    <span class="text-navy/30 text-sm">The Davidson Family — photo coming soon</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5 space-y-8">
                {{-- The Family --}}
                <div>
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-4">Meet the Davidsons</h2>
                    <div class="space-y-4 text-navy/70 leading-relaxed">
                        <p>We're Jeffrey and Cassie, and we live in Central Florida with our daughter Viola, who's 8 years old. We're the kind of family that goes to Disney World every single week — not because we're obsessed (okay, maybe a little), but because it's genuinely become one of the best things for our family.</p>
                        <p>When you live 30 minutes from the most magical place on Earth, you'd be surprised how quickly it becomes your go-to spot for Saturday afternoons, birthday celebrations, and even "we just need to get out of the house" moments.</p>
                    </div>
                </div>

                {{-- Viola's Story --}}
                <div class="border-t border-navy/10 pt-8">
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-4">Viola's World</h2>
                    <div class="space-y-4 text-navy/70 leading-relaxed">
                        <p>Viola was diagnosed with autism when she was 3. Like a lot of parents, we went through a period of figuring things out — what works, what doesn't, what environments help her thrive, and which ones overwhelm her.</p>
                        <p>Disney turned out to be one of those places that just <em>works</em> for Viola. Not always perfectly — we've had our share of meltdowns in Tomorrowland and sensory overload in the gift shops. But Disney's accessibility programs, especially the DAS (Disability Access Service) pass, have been a game-changer for our family.</p>
                        <p>More than that, Viola experiences the parks in ways we never would have noticed on our own. She'll spend 20 minutes watching the water patterns in a fountain. She knows every character meet-and-greet by heart. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                    </div>
                </div>

                {{-- Why the Podcast --}}
                <div class="border-t border-navy/10 pt-8">
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-4">Why Mouse'28?</h2>
                    <div class="space-y-4 text-navy/70 leading-relaxed">
                        <p>The name comes from 1928 — the year Mickey Mouse made his debut in <em>Steamboat Willie</em>. It felt right: the beginning of Disney magic, just like Viola is the beginning of ours.</p>
                        <p>We started Mouse'28 because we kept getting the same questions from other autism families: <em>"How do you do Disney with your kid?" "Is the DAS pass worth it?" "What rides are sensory-friendly?"</em></p>
                        <p>We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently. Tips about quiet spots when things get overwhelming. Strategies for wait times. Which characters are the most patient. The best times to visit specific areas.</p>
                        <p>So we hit record. And it turns out there are a lot of families out there who needed to hear exactly this.</p>
                    </div>
                </div>

                {{-- What We Cover --}}
                <div class="border-t border-navy/10 pt-8">
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-4">What We Talk About</h2>
                    <div class="grid sm:grid-cols-2 gap-4 mt-6">
                        <div class="bg-cream rounded-xl p-5">
                            <span class="text-2xl mb-2 block">🎫</span>
                            <h3 class="font-heading font-semibold text-navy mb-1">DAS Pass Tips</h3>
                            <p class="text-navy/60 text-sm">How to apply, what to expect, and making the most of disability accommodations.</p>
                        </div>
                        <div class="bg-cream rounded-xl p-5">
                            <span class="text-2xl mb-2 block">🧘</span>
                            <h3 class="font-heading font-semibold text-navy mb-1">Sensory-Friendly Guide</h3>
                            <p class="text-navy/60 text-sm">Quiet spaces, low-stimulation zones, and rides rated by sensory intensity.</p>
                        </div>
                        <div class="bg-cream rounded-xl p-5">
                            <span class="text-2xl mb-2 block">🏰</span>
                            <h3 class="font-heading font-semibold text-navy mb-1">Park Accessibility</h3>
                            <p class="text-navy/60 text-sm">Real-world reviews of how accessible Disney parks actually are for diverse needs.</p>
                        </div>
                        <div class="bg-cream rounded-xl p-5">
                            <span class="text-2xl mb-2 block">❤️</span>
                            <h3 class="font-heading font-semibold text-navy mb-1">Family Disney Life</h3>
                            <p class="text-navy/60 text-sm">The funny, messy, magical reality of weekly Disney trips with our family.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="text-center mt-12">
                <p class="text-navy/60 mb-4">Ready to join our Disney family?</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/episodes" class="bg-purple hover:bg-purple-dark text-white font-semibold px-6 py-3 rounded-full transition-all">🎙️ Listen to Episodes</a>
                    <a href="#subscribe" class="bg-gold hover:bg-gold-light text-navy font-semibold px-6 py-3 rounded-full transition-all">✨ Subscribe</a>
                </div>
            </div>
        </div>
    </section>
@endsection
