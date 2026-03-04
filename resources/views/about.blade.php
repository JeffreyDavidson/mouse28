@extends('layouts.app')

@section('title', 'About - Mouse28')
@section('meta_description', 'Meet the Davidsons. Jeffrey, Cassie, and Viola. Learn how our family navigates Disney parks with autism and why we started Mouse28.')

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

    {{-- Timeline Story --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center mb-14">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Our Journey</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-3">How We Got Here</h2>
            <p class="text-navy/50 mt-4 max-w-lg mx-auto font-body leading-relaxed">From first-time park visitors to weekly regulars, here's the story behind Mouse28.</p>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            {{-- Section: Meet the Davidsons --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter One</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Meet the Davidsons</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>We're Jeffrey and Cassie, and we live in Central Florida with our daughter Viola, who's 8 years old. We're the kind of family that goes to Disney World every single week, not because we're obsessed (okay, maybe a little), but because it's genuinely become one of the best things for our family.</p>
                    <p>When you live 30 minutes from the most magical place on Earth, you'd be surprised how quickly it becomes your go-to spot for Saturday afternoons, birthday celebrations, and even "we just need to get out of the house" moments.</p>
                </div>
            </div>

            {{-- Section: Viola's World --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter Two</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Viola's World</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>Viola was diagnosed with autism when she was 5. Like a lot of parents, we went through a period of figuring things out: what works, what doesn't, what environments help her thrive, and which ones overwhelm her.</p>
                    <p>Disney turned out to be one of those places that just <em>works</em> for Viola. Not always perfectly. We've had our share of meltdowns in Tomorrowland and sensory overload in the gift shops. But Disney's accessibility programs, especially the DAS (Disability Access Service) pass, have been a game-changer for our family.</p>
                    <p>More than that, Viola experiences the parks in ways we never would have noticed on our own. She'll spend 20 minutes watching the water patterns in a fountain. She knows every character meet-and-greet by heart. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                </div>
            </div>

            {{-- Section: Why Mouse28 --}}
            <div class="relative pl-8 md:pl-12 border-l-2 border-gold/30 pb-16">
                <div class="absolute left-0 top-0 w-4 h-4 bg-gold rounded-full -translate-x-[9px] ring-4 ring-cream"></div>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase">Chapter Three</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-2 mb-5">Why Mouse28?</h2>
                <div class="space-y-4 text-navy/70 leading-relaxed">
                    <p>The name comes from 1928, the year Mickey Mouse made his debut in <em>Steamboat Willie</em>. It felt right: the beginning of Disney magic, just like Viola is the beginning of ours.</p>
                    <p>We started Mouse28 because we kept getting the same questions from other autism families: <em>"How do you do Disney with your kid?" "Is the DAS pass worth it?" "What rides are sensory-friendly?"</em></p>
                    <p>We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently. Tips about quiet spots when things get overwhelming. Strategies for wait times. Which characters are the most patient. The best times to visit specific areas.</p>
                    <p>So we hit record. And it turns out there are a lot of families out there who needed to hear exactly this.</p>
                </div>
            </div>

        </div>
    </section>

    {{-- Quote Banner --}}
    <section class="relative overflow-hidden py-20 md:py-28" style="background: linear-gradient(135deg, #1a1040 0%, #2d1a5e 50%, #1a1040 100%);">
        <!-- Subtle dot pattern -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>

        <!-- Top diagonal cut -->
        <div class="absolute top-0 left-0 w-full overflow-hidden" style="height: 60px;">
            <div class="absolute w-full h-full" style="background: #fef9ef; clip-path: polygon(0 0, 100% 0, 100% 20%, 0 100%);"></div>
        </div>
        <!-- Bottom diagonal cut -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden" style="height: 60px;">
            <div class="absolute w-full h-full" style="background: #fef9ef; clip-path: polygon(0 80%, 100% 0, 100% 100%, 0 100%);"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-6 md:px-12">
            <div class="grid md:grid-cols-2 gap-12 md:gap-16">
                <!-- Quote 1 -->
                <div class="relative">
                    <svg class="w-10 h-10 mb-4" style="color: #d4a843; opacity: 0.3;" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/></svg>
                    <p class="font-heading text-xl md:text-2xl text-white/85 italic leading-relaxed mb-6">Viola experiences the parks in ways we never would have noticed on our own. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                    <div class="h-px w-12" style="background: linear-gradient(90deg, #d4a843, transparent);"></div>
                </div>

                <!-- Quote 2 -->
                <div class="relative">
                    <svg class="w-10 h-10 mb-4" style="color: #5b3e9e; opacity: 0.4;" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/></svg>
                    <p class="font-heading text-xl md:text-2xl text-white/85 italic leading-relaxed mb-6">We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently.</p>
                    <div class="h-px w-12" style="background: linear-gradient(90deg, #5b3e9e, transparent);"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Your Hosts --}}
    <section class="py-20 md:py-28 bg-cream relative overflow-hidden">
        {{-- Subtle background pattern --}}
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, #1a1040 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="max-w-5xl mx-auto px-6 md:px-12 relative z-10">
            <div class="text-center mb-16">
                <span class="inline-block tracking-widest uppercase text-xs font-semibold mb-4 px-6 py-2 rounded-full border font-body" style="color: #5b3e9e; border-color: rgba(91,62,158,0.2); background: rgba(91,62,158,0.05); letter-spacing: 0.25em;">Your Hosts</span>
                <h2 class="font-heading text-3xl md:text-5xl font-bold text-navy mt-3">The Voices Behind<br><span class="text-gold italic">Mouse28</span></h2>
            </div>

            {{-- Jeffrey --}}
            <div class="relative mb-16 md:mb-24">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-16 items-center">
                    {{-- Photo area --}}
                    <div class="md:col-span-5 relative">
                        <div class="relative rounded-2xl overflow-hidden" style="aspect-ratio: 3/4;">
                            <img src="/images/jeffrey.jpg" alt="Jeffrey Davidson at Epcot's Japan Pavilion" class="absolute inset-0 w-full h-full object-cover object-top">
                            {{-- Name overlay at bottom --}}
                            <div class="absolute bottom-0 left-0 right-0 p-6" style="background: linear-gradient(to top, rgba(26,16,64,0.95), transparent);">
                                <span class="uppercase tracking-widest text-xs font-semibold font-body" style="color: #d4a843; letter-spacing: 0.2em;">Co-Host</span>
                            </div>
                        </div>
                        {{-- Offset accent --}}
                        <div class="hidden md:block absolute -bottom-4 -right-4 w-full h-full rounded-2xl -z-10" style="border: 1px solid rgba(212,168,67,0.15);"></div>
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-7">
                        <h3 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-2">Jeffrey Davidson</h3>
                        <p class="font-body text-sm font-semibold mb-6" style="color: #5b3e9e;">Dad, Software Engineer, Disney Strategist</p>
                        <div class="space-y-4">
                            <p class="text-navy/65 font-body leading-relaxed">The planner. The podcast editor. The guy who can navigate World Showcase blindfolded and will debate the best Epcot festival food until closing time.</p>
                            <p class="text-navy/65 font-body leading-relaxed">Jeffrey is a software engineer by trade with over 15 years of experience building things on the internet. When he's not coding, he's mapping out the most efficient park route or convincing Cassie they need "just one more ride" before heading home.</p>
                        </div>
                        {{-- Fun facts --}}
                        <div class="mt-8 flex flex-wrap gap-3">
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(212,168,67,0.15); border: 1px solid rgba(212,168,67,0.2);">Favorite park: Epcot</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(91,62,158,0.1); border: 1px solid rgba(91,62,158,0.15);">Yankees forever ⚾</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(212,168,67,0.15); border: 1px solid rgba(212,168,67,0.2);">Code & coffee</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="flex items-center justify-center gap-4 mb-16 md:mb-24">
                <div class="h-px flex-1 max-w-[100px]" style="background: linear-gradient(90deg, transparent, rgba(212,168,67,0.3));"></div>
                <svg class="w-5 h-5" style="color: #d4a843; opacity: 0.4;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <div class="h-px flex-1 max-w-[100px]" style="background: linear-gradient(90deg, rgba(212,168,67,0.3), transparent);"></div>
            </div>

            {{-- Cassie (reversed) --}}
            <div class="relative">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-16 items-center">
                    {{-- Bio (left on desktop) --}}
                    <div class="md:col-span-7 order-2 md:order-1">
                        <h3 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-2">Cassie Davidson</h3>
                        <p class="font-body text-sm font-semibold mb-6" style="color: #5b3e9e;">Mom, Baker, Accessibility Champion</p>
                        <div class="space-y-4">
                            <p class="text-navy/65 font-body leading-relaxed">The heart of Mouse28. Cassie is the one who makes sure every tip we share actually helps real families. She brings warmth, honesty, and a park snack bag that has never once let us down.</p>
                            <p class="text-navy/65 font-body leading-relaxed">When she's not at the parks, Cassie runs a cottage food bakery, wrangles two huskies, and somehow keeps everything running smoothly. She's the reason this whole thing works.</p>
                        </div>
                        {{-- Fun facts --}}
                        <div class="mt-8 flex flex-nowrap gap-3">
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium whitespace-nowrap" style="color: #1a1040; background: rgba(91,62,158,0.1); border: 1px solid rgba(91,62,158,0.15);">Favorite park: Magic Kingdom</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium whitespace-nowrap" style="color: #1a1040; background: rgba(212,168,67,0.15); border: 1px solid rgba(212,168,67,0.2);">Cottage food baker</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium whitespace-nowrap" style="color: #1a1040; background: rgba(91,62,158,0.1); border: 1px solid rgba(91,62,158,0.15);">Rock Chalk Jayhawk</span>
                        </div>
                    </div>

                    {{-- Photo area (right on desktop) --}}
                    <div class="md:col-span-5 relative order-1 md:order-2">
                        <div class="relative rounded-2xl overflow-hidden" style="aspect-ratio: 3/4;">
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, #5b3e9e, #1a1040);"></div>
                            {{-- Placeholder until real photo --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="w-24 h-24 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: rgba(212,168,67,0.15); border: 2px solid rgba(212,168,67,0.2);">
                                        <svg class="w-12 h-12" style="color: rgba(212,168,67,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                    </div>
                                    <p class="text-white/20 text-xs font-body">Photo coming soon</p>
                                </div>
                            </div>
                            {{-- Name overlay at bottom --}}
                            <div class="absolute bottom-0 left-0 right-0 p-6" style="background: linear-gradient(to top, rgba(26,16,64,0.95), transparent);">
                                <span class="uppercase tracking-widest text-xs font-semibold font-body" style="color: #d4a843; letter-spacing: 0.2em;">Co-Host</span>
                            </div>
                        </div>
                        {{-- Offset accent --}}
                        <div class="hidden md:block absolute -bottom-4 -left-4 w-full h-full rounded-2xl -z-10" style="border: 1px solid rgba(91,62,158,0.15);"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Sign-off --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden py-16 md:py-20">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <p class="font-heading text-2xl md:text-3xl text-white/90 italic leading-relaxed mb-3">Thanks for getting to know us.</p>
            <p class="font-heading text-2xl md:text-3xl text-gold italic leading-relaxed mb-10">Now let's get to the good stuff.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/blog" class="bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">Read the Blog</a>
                <a href="/episodes" class="bg-white/10 hover:bg-white/15 text-white font-semibold px-8 py-3.5 rounded-full transition-all hover:-translate-y-0.5 border border-white/10">Listen to the Podcast</a>
            </div>
        </div>
    </section>
@endsection
