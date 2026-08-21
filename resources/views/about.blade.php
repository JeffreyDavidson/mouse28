@extends('layouts.app')

@section('title', 'About - Mouse28')
@section('meta_description', 'Meet the Davidsons. Jeffrey, Cassie, and Viola. Learn how our family navigates Disney parks with autism and why we started Mouse28.')

@section('content')
    {{-- Hero with sparkles --}}
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-24">
        {{-- Sparkles --}}
        <div class="pointer-events-none absolute inset-0">
            <div class="sparkle absolute top-12 left-[15%] text-lg text-gold/60">✦</div>
            <div class="sparkle-delay absolute top-20 right-[20%] text-sm text-gold/40">✦</div>
            <div class="sparkle-delay-2 absolute bottom-16 left-[30%] text-xl text-gold/50">✦</div>
            <div class="sparkle absolute right-[10%] bottom-20 text-base text-gold/30">✦</div>
            <div class="sparkle-delay absolute top-1/2 left-[8%] text-sm text-purple-light/40">✦</div>
            <div class="sparkle-delay-2 absolute top-16 right-[40%] text-gold/40">✦</div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 text-center sm:px-6">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                <span class="size-2 animate-pulse rounded-full bg-gold"></span>
                <span class="text-sm font-semibold tracking-widest text-gold uppercase">About Us</span>
            </div>
            <h1 class="mt-2 font-heading text-4xl font-bold text-white md:text-5xl lg:text-6xl">Our Story</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/60">How a family, a little girl, and a whole lot of Disney magic became something worth sharing.</p>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section class="border-t border-white/10 bg-navy-light">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            <div class="grid grid-cols-2 gap-6 text-center md:grid-cols-4">
                <div>
                    <div class="font-heading text-4xl font-bold text-gold md:text-5xl">150+</div>
                    <div class="mt-1.5 text-sm text-white/50">Weekly Visits</div>
                </div>
                <div>
                    <div class="font-heading text-4xl font-bold text-gold md:text-5xl">4</div>
                    <div class="mt-1.5 text-sm text-white/50">Parks</div>
                </div>
                <div>
                    <div class="font-heading text-4xl font-bold text-gold md:text-5xl">1</div>
                    <div class="mt-1.5 text-sm text-white/50">Amazing Daughter</div>
                </div>
                <div>
                    <div class="font-heading text-4xl font-bold text-gold md:text-5xl">∞</div>
                    <div class="mt-1.5 text-sm text-white/50">Memories</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Timeline Story --}}
    <section class="bg-cream py-16 md:py-24">
        <div class="mx-auto mb-14 max-w-4xl px-4 text-center sm:px-6">
            <span class="text-sm font-semibold tracking-widest text-gold uppercase">Our Journey</span>
            <h2 class="mt-3 font-heading text-3xl font-bold text-navy md:text-4xl">How We Got Here</h2>
            <p class="mx-auto mt-4 max-w-lg font-body leading-relaxed text-navy/50">From first-time park visitors to weekly regulars, here's the story behind Mouse28.</p>
        </div>
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            {{-- Section: Meet the Davidsons --}}
            <div class="relative border-l-2 border-gold/30 pb-16 pl-8 md:pl-12">
                <div class="absolute top-0 left-0 size-4 translate-x-[-9px] rounded-full bg-gold ring-4 ring-cream"></div>
                <span class="text-xs font-semibold tracking-widest text-gold uppercase">Chapter One</span>
                <h2 class="mt-2 mb-5 font-heading text-2xl font-bold text-navy md:text-3xl">Meet the Davidsons</h2>
                <div class="space-y-4 leading-relaxed text-navy/70">
                    <p>We're Jeffrey and Cassie, and we live in Central Florida with our daughter Viola, who's 8 years old. We're the kind of family that goes to Disney World every single week, not because we're obsessed (okay, maybe a little), but because it's genuinely become one of the best things for our family.</p>
                    <p>When you live 30 minutes from the most magical place on Earth, you'd be surprised how quickly it becomes your go-to spot for Saturday afternoons, birthday celebrations, and even "we just need to get out of the house" moments.</p>
                </div>
            </div>

            {{-- Section: Viola's World --}}
            <div class="relative border-l-2 border-gold/30 pb-16 pl-8 md:pl-12">
                <div class="absolute top-0 left-0 size-4 translate-x-[-9px] rounded-full bg-gold ring-4 ring-cream"></div>
                <span class="text-xs font-semibold tracking-widest text-gold uppercase">Chapter Two</span>
                <h2 class="mt-2 mb-5 font-heading text-2xl font-bold text-navy md:text-3xl">Viola's World</h2>
                <div class="space-y-4 leading-relaxed text-navy/70">
                    <p>Viola was diagnosed with autism when she was 5. Like a lot of parents, we went through a period of figuring things out: what works, what doesn't, what environments help her thrive, and which ones overwhelm her.</p>
                    <p>Disney turned out to be one of those places that just <em>works</em> for Viola. Not always perfectly. We've had our share of meltdowns in Tomorrowland and sensory overload in the gift shops. But Disney's accessibility programs, especially the DAS (Disability Access Service) pass, have been a game-changer for our family.</p>
                    <p>More than that, Viola experiences the parks in ways we never would have noticed on our own. She'll spend 20 minutes watching the water patterns in a fountain. She knows every character meet-and-greet by heart. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                </div>
            </div>

            {{-- Section: Why Mouse28 --}}
            <div class="relative border-l-2 border-gold/30 pb-16 pl-8 md:pl-12">
                <div class="absolute top-0 left-0 size-4 translate-x-[-9px] rounded-full bg-gold ring-4 ring-cream"></div>
                <span class="text-xs font-semibold tracking-widest text-gold uppercase">Chapter Three</span>
                <h2 class="mt-2 mb-5 font-heading text-2xl font-bold text-navy md:text-3xl">Why Mouse28?</h2>
                <div class="space-y-4 leading-relaxed text-navy/70">
                    <p>The name comes from 1928, the year Mickey Mouse made his debut in <em>Steamboat Willie</em>. It felt right: the beginning of Disney magic, just like Viola is the beginning of ours.</p>
                    <p>We started Mouse28 because we kept getting the same questions from other autism families: <em>"How do you do Disney with your kid?" "Is the DAS pass worth it?" "What rides are sensory-friendly?"</em></p>
                    <p>We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently. Tips about quiet spots when things get overwhelming. Strategies for wait times. Which characters are the most patient. The best times to visit specific areas.</p>
                    <p>So we hit record. And it turns out there are a lot of families out there who needed to hear exactly this.</p>
                </div>
            </div>

        </div>
    </section>

    {{-- Quote Banner --}}
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-24">
        <!-- Subtle dot pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,white_1px,transparent_0)] bg-size-[40px_40px] opacity-[0.03]"></div>

        <!-- Top diagonal cut -->
        <div class="absolute top-0 left-0 h-[60px] w-full overflow-hidden">
            <div class="absolute size-full bg-cream [clip-path:polygon(0_0,100%_0,100%_20%,0_100%)]"></div>
        </div>
        <!-- Bottom diagonal cut -->
        <div class="absolute bottom-0 left-0 h-[60px] w-full overflow-hidden">
            <div class="absolute size-full bg-cream [clip-path:polygon(0_80%,100%_0,100%_100%,0_100%)]"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-4xl px-6 md:px-12">
            <div class="grid gap-12 md:grid-cols-2 md:gap-16">
                <!-- Quote 1 -->
                <div class="relative">
                    <svg class="mb-4 size-10 text-gold/30" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/></svg>
                    <p class="mb-6 font-heading text-xl/relaxed text-white/85 italic md:text-2xl">Viola experiences the parks in ways we never would have noticed on our own. She's taught us that slowing down and paying attention is where the real magic lives.</p>
                    <div class="h-px w-12 bg-linear-to-r from-gold to-transparent"></div>
                </div>

                <!-- Quote 2 -->
                <div class="relative">
                    <svg class="mb-4 size-10 text-purple/40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/></svg>
                    <p class="mb-6 font-heading text-xl/relaxed text-white/85 italic md:text-2xl">We realized we had years of hard-won knowledge about navigating Disney parks with a child who experiences the world differently.</p>
                    <div class="h-px w-12 bg-linear-to-r from-purple to-transparent"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Your Hosts --}}
    <section class="relative overflow-hidden bg-cream py-16 md:py-24">
        {{-- Subtle background pattern --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,#1a1040_1px,transparent_0)] bg-size-[32px_32px] opacity-[0.02]"></div>

        <div class="relative z-10 mx-auto max-w-5xl px-6 md:px-12">
            <div class="mb-16 text-center">
                <span class="mb-4 inline-block rounded-full border border-purple/20 bg-purple/5 px-6 py-2 font-body text-xs font-semibold tracking-[0.25em] text-purple uppercase">Your Hosts</span>
                <h2 class="mt-3 font-heading text-3xl font-bold text-navy md:text-5xl">The Voices Behind<br><span class="text-gold italic">Mouse28</span></h2>
            </div>

            {{-- Jeffrey --}}
            <div class="relative mb-16 md:mb-24">
                <div class="grid grid-cols-1 items-center gap-8 md:grid-cols-12 md:gap-16">
                    {{-- Photo area --}}
                    <div class="relative md:col-span-5">
                        <div class="relative aspect-3/4 overflow-hidden rounded-2xl">
                            <img src="/images/jeffrey.jpg" alt="Jeffrey Davidson at Epcot's Japan Pavilion" class="absolute inset-0 size-full object-cover object-top">
                            {{-- Name overlay at bottom --}}
                            <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-navy/95 to-transparent p-6">
                                <span class="font-body text-xs font-semibold tracking-[0.2em] text-gold uppercase">Co-Host</span>
                            </div>
                        </div>
                        {{-- Offset accent --}}
                        <div class="absolute -right-4 -bottom-4 -z-10 hidden size-full rounded-2xl border border-gold/15 md:block"></div>
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-7">
                        <h3 class="mb-2 font-heading text-2xl font-bold text-navy md:text-3xl">Jeffrey Davidson</h3>
                        <p class="mb-6 font-body text-base font-semibold text-purple sm:text-sm">Dad, Software Engineer, Disney Strategist</p>
                        <div class="space-y-4">
                            <p class="font-body leading-relaxed text-navy/65">The planner. The podcast editor. The guy who can navigate World Showcase blindfolded and will debate the best Epcot festival food until closing time.</p>
                            <p class="font-body leading-relaxed text-navy/65">Jeffrey is a software engineer by trade with over 15 years of experience building things on the internet. When he's not coding, he's mapping out the most efficient park route or convincing Cassie they need "just one more ride" before heading home.</p>
                        </div>
                        {{-- Fun facts --}}
                        <div class="mt-8 flex flex-wrap gap-3">
                            <span class="rounded-full border border-gold/20 bg-gold/15 px-4 py-2 font-body text-xs font-medium text-navy">Favorite park: Epcot</span>
                            <span class="rounded-full border border-purple/15 bg-purple/10 px-4 py-2 font-body text-xs font-medium text-navy">Yankees forever ⚾</span>
                            <span class="rounded-full border border-gold/20 bg-gold/15 px-4 py-2 font-body text-xs font-medium text-navy">Code & coffee</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="mb-16 flex items-center justify-center gap-4 md:mb-24">
                <div class="h-px max-w-[100px] flex-1 bg-linear-to-r from-transparent to-gold/30"></div>
                <svg class="size-5 text-gold/40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <div class="h-px max-w-[100px] flex-1 bg-linear-to-r from-gold/30 to-transparent"></div>
            </div>

            {{-- Cassie (reversed) --}}
            <div class="relative">
                <div class="grid grid-cols-1 items-center gap-8 md:grid-cols-12 md:gap-16">
                    {{-- Bio (left on desktop) --}}
                    <div class="order-2 md:order-1 md:col-span-7">
                        <h3 class="mb-2 font-heading text-2xl font-bold text-navy md:text-3xl">Cassie Davidson</h3>
                        <p class="mb-6 font-body text-base font-semibold text-purple sm:text-sm">Mom, Baker, Accessibility Champion</p>
                        <div class="space-y-4">
                            <p class="font-body leading-relaxed text-navy/65">The heart of Mouse28. Cassie is the one who makes sure every tip we share actually helps real families. She brings warmth, honesty, and a park snack bag that has never once let us down.</p>
                            <p class="font-body leading-relaxed text-navy/65">When she's not at the parks, Cassie runs a cottage food bakery, wrangles two huskies, and somehow keeps everything running smoothly. She's the reason this whole thing works.</p>
                        </div>
                        {{-- Fun facts --}}
                        <div class="mt-8 flex flex-wrap gap-3">
                            <span class="rounded-full border border-purple/15 bg-purple/10 px-4 py-2 font-body text-xs font-medium whitespace-nowrap text-navy">Favorite park: Magic Kingdom</span>
                            <span class="rounded-full border border-gold/20 bg-gold/15 px-4 py-2 font-body text-xs font-medium whitespace-nowrap text-navy">Cottage food baker</span>
                            <span class="rounded-full border border-purple/15 bg-purple/10 px-4 py-2 font-body text-xs font-medium whitespace-nowrap text-navy">Rock Chalk Jayhawk</span>
                        </div>
                    </div>

                    {{-- Photo area (right on desktop) --}}
                    <div class="relative order-1 md:order-2 md:col-span-5">
                        <div class="relative aspect-3/4 overflow-hidden rounded-2xl">
                            <img src="/images/cassie.jpg" alt="Cassie Davidson at Magic Kingdom's Winnie the Pooh" class="absolute inset-0 size-full object-cover object-top">
                            {{-- Name overlay at bottom --}}
                            <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-navy/95 to-transparent p-6">
                                <span class="font-body text-xs font-semibold tracking-[0.2em] text-gold uppercase">Co-Host</span>
                            </div>
                        </div>
                        {{-- Offset accent --}}
                        <div class="absolute -bottom-4 -left-4 -z-10 hidden size-full rounded-2xl border border-purple/15 md:block"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Sign-off --}}
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="relative z-10 mx-auto max-w-2xl px-4 text-center sm:px-6">
            <p class="mb-3 font-heading text-2xl/relaxed text-white/90 italic md:text-3xl">Thanks for getting to know us.</p>
            <p class="mb-10 font-heading text-2xl/relaxed text-gold italic md:text-3xl">Now let's get to the good stuff.</p>
            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('blog.index') }}" class="rounded-full bg-gold px-8 py-3.5 font-semibold text-navy transition-[transform,background-color,box-shadow] hover:-translate-y-0.5 hover:bg-gold-light hover:shadow-lg hover:shadow-gold/25">Read the Blog</a>
                <a href="{{ route('episodes.index') }}" class="rounded-full border border-white/10 bg-white/10 px-8 py-3.5 font-semibold text-white transition-[transform,background-color] hover:-translate-y-0.5 hover:bg-white/15">Listen to the Podcast</a>
            </div>
        </div>
    </section>
@endsection
