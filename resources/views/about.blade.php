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
                    <span class="text-navy/30 text-sm">The Davidson Family, photo coming soon</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Timeline Story --}}
    <section class="py-16 md:py-24 bg-cream">
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
                    <p>The name comes from 1928 — the year Mickey Mouse made his debut in <em>Steamboat Willie</em>. It felt right: the beginning of Disney magic, just like Viola is the beginning of ours.</p>
                    <p>We started Mouse28 because we kept getting the same questions from other autism families: <em>"How do you do Disney with your kid?" "Is the DAS pass worth it?" "What rides are sensory-friendly?"</em></p>
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
    <!-- What We Cover Section -->
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a1040 0%, #2d1a5e 50%, #1a1040 100%);">

      <!-- Decorative background elements -->
      <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 20% 50%, rgba(212,168,67,0.15) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(91,62,158,0.2) 0%, transparent 40%), radial-gradient(circle at 60% 80%, rgba(212,168,67,0.1) 0%, transparent 45%);"></div>
      </div>

      <!-- Top diagonal cut -->
      <div class="absolute top-0 left-0 w-full overflow-hidden" style="height: 80px;">
        <div class="absolute w-full h-full" style="background: #fef9ef; clip-path: polygon(0 0, 100% 0, 100% 30%, 0 100%);"></div>
      </div>

      <!-- Bottom diagonal cut -->
      <div class="absolute bottom-0 left-0 w-full overflow-hidden" style="height: 80px;">
        <div class="absolute w-full h-full" style="background: #fef9ef; clip-path: polygon(0 70%, 100% 0, 100% 100%, 0 100%);"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-6 md:px-12" style="padding-top: 140px; padding-bottom: 140px;">

        <!-- Section Header -->
        <div class="text-center mb-6">
          <span class="inline-block tracking-widest uppercase text-xs font-semibold mb-4 px-6 py-2 rounded-full border" style="font-family: 'Poppins', sans-serif; color: #d4a843; border-color: rgba(212,168,67,0.3); background: rgba(212,168,67,0.05); letter-spacing: 0.25em;">What We Cover</span>
        </div>
        <h2 class="text-center mb-4" style="font-family: 'Playfair Display', serif; color: #fef9ef; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; font-weight: 700;">
          Three Worlds.<br>
          <span style="color: #d4a843; font-style: italic;">One Mission.</span>
        </h2>
        <p class="text-center max-w-xl mx-auto mb-20 opacity-70" style="font-family: 'Poppins', sans-serif; color: #fef9ef; font-size: 1.05rem; line-height: 1.7;">
          Making Disney accessible, honest, and a whole lot more fun for families like ours.
        </p>

        <!-- TOPIC 01 — DAS Pass Tips -->
        <div class="relative mb-32 md:mb-40">
          <!-- Oversized number -->
          <div class="absolute -top-8 md:-top-16 left-0 md:left-8 select-none pointer-events-none" style="font-family: 'Playfair Display', serif; font-size: clamp(8rem, 18vw, 14rem); font-weight: 900; line-height: 1; color: rgba(212,168,67,0.07);">01</div>

          <div class="relative grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <!-- Text block -->
            <div class="md:col-span-5 md:col-start-1 relative z-10">
              <div class="relative p-8 md:p-10 rounded-2xl" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(212,168,67,0.12);">
                <div class="flex items-center gap-3 mb-5">
                  <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background: linear-gradient(135deg, #d4a843, #e8c36a);">
                    <svg class="w-5 h-5" fill="none" stroke="#1a1040" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  </div>
                </div>
                <h3 class="mb-4" style="font-family: 'Playfair Display', serif; color: #fef9ef; font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2;">
                  DAS Pass<br><span style="color: #d4a843; font-style: italic;">Tips</span>
                </h3>
                <p style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.65); font-size: 0.95rem; line-height: 1.8;">
                  Navigating Disney's Disability Access Service can feel overwhelming. We break it down with real experience, current policies, and practical strategies that actually work for neurodivergent families.
                </p>
                <!-- Decorative line -->
                <div class="mt-6 h-px w-16" style="background: linear-gradient(90deg, #d4a843, transparent);"></div>
              </div>
            </div>

            <!-- Visual block -->
            <div class="md:col-span-6 md:col-start-7 relative">
              <div class="relative rounded-2xl overflow-hidden" style="aspect-ratio: 4/3; background: linear-gradient(135deg, #5b3e9e, #1a1040);">
                <!-- Abstract pattern -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center">
                    <div style="font-family: 'Playfair Display', serif; font-size: clamp(4rem, 8vw, 6rem); font-weight: 900; color: rgba(212,168,67,0.12); line-height: 1;">DAS</div>
                    <div class="mt-2 flex items-center justify-center gap-2">
                      <div class="h-px w-8" style="background: rgba(212,168,67,0.3);"></div>
                      <svg class="w-4 h-4" style="color: #d4a843; opacity: 0.5;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                      <div class="h-px w-8" style="background: rgba(212,168,67,0.3);"></div>
                    </div>
                  </div>
                </div>
                <!-- Floating stats -->
                <div class="absolute bottom-4 left-4 right-4 flex gap-3">
                  <div class="flex-1 rounded-xl p-4 text-center" style="background: rgba(26,16,64,0.85); backdrop-filter: blur(10px); border: 1px solid rgba(212,168,67,0.15);">
                    <div class="font-bold" style="font-family: 'Playfair Display', serif; color: #d4a843; font-size: 1.5rem;">4</div>
                    <div class="text-xs mt-1 opacity-60" style="font-family: 'Poppins', sans-serif; color: #fef9ef;">Parks Covered</div>
                  </div>
                  <div class="flex-1 rounded-xl p-4 text-center" style="background: rgba(26,16,64,0.85); backdrop-filter: blur(10px); border: 1px solid rgba(212,168,67,0.15);">
                    <div class="font-bold" style="font-family: 'Playfair Display', serif; color: #d4a843; font-size: 1.5rem;">Weekly</div>
                    <div class="text-xs mt-1 opacity-60" style="font-family: 'Poppins', sans-serif; color: #fef9ef;">Park Visits</div>
                  </div>
                  <div class="flex-1 rounded-xl p-4 text-center" style="background: rgba(26,16,64,0.85); backdrop-filter: blur(10px); border: 1px solid rgba(212,168,67,0.15);">
                    <div class="font-bold" style="font-family: 'Playfair Display', serif; color: #d4a843; font-size: 1.5rem;">Real</div>
                    <div class="text-xs mt-1 opacity-60" style="font-family: 'Poppins', sans-serif; color: #fef9ef;">Experience</div>
                  </div>
                </div>
              </div>
              <!-- Offset accent block -->
              <div class="hidden md:block absolute -bottom-6 -right-6 w-32 h-32 rounded-2xl -z-10" style="background: linear-gradient(135deg, rgba(212,168,67,0.15), rgba(91,62,158,0.1)); border: 1px solid rgba(212,168,67,0.08);"></div>
            </div>
          </div>
        </div>

        <!-- TOPIC 02 — Park Experiences (reversed layout) -->
        <div class="relative mb-32 md:mb-40">
          <!-- Oversized number -->
          <div class="absolute -top-8 md:-top-16 right-0 md:right-8 select-none pointer-events-none text-right" style="font-family: 'Playfair Display', serif; font-size: clamp(8rem, 18vw, 14rem); font-weight: 900; line-height: 1; color: rgba(91,62,158,0.1);">02</div>

          <div class="relative grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <!-- Visual block (left on desktop) -->
            <div class="md:col-span-6 md:col-start-1 relative order-2 md:order-1">
              <div class="relative rounded-2xl overflow-hidden" style="aspect-ratio: 4/3; background: linear-gradient(135deg, #1a1040, #5b3e9e);">
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center px-6">
                    <div style="font-family: 'Playfair Display', serif; font-size: clamp(3rem, 6vw, 4.5rem); font-weight: 900; color: rgba(254,249,239,0.06); line-height: 1;">PARKS</div>
                    <!-- Stacked horizontal lines -->
                    <div class="mt-6 space-y-2 flex flex-col items-center">
                      <div class="h-px rounded-full" style="width: 120px; background: rgba(212,168,67,0.3);"></div>
                      <div class="h-px rounded-full" style="width: 80px; background: rgba(212,168,67,0.2);"></div>
                      <div class="h-px rounded-full" style="width: 40px; background: rgba(212,168,67,0.1);"></div>
                    </div>
                  </div>
                </div>
                <!-- Pull quote overlay -->
                <div class="absolute top-6 left-6 right-6">
                  <div class="rounded-xl p-5" style="background: rgba(26,16,64,0.85); backdrop-filter: blur(10px); border: 1px solid rgba(91,62,158,0.2);">
                    <svg class="w-6 h-6 mb-2" style="color: #d4a843; opacity: 0.6;" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983z"/></svg>
                    <p style="font-family: 'Playfair Display', serif; color: rgba(254,249,239,0.8); font-size: 1rem; font-style: italic; line-height: 1.6;">
                      We ride every ride, test every queue, and tell you what it's really like.
                    </p>
                  </div>
                </div>
              </div>
              <!-- Offset accent block -->
              <div class="hidden md:block absolute -bottom-6 -left-6 w-32 h-32 rounded-2xl -z-10" style="background: linear-gradient(135deg, rgba(91,62,158,0.15), rgba(212,168,67,0.05)); border: 1px solid rgba(91,62,158,0.1);"></div>
            </div>

            <!-- Text block (right on desktop) -->
            <div class="md:col-span-5 md:col-start-8 relative z-10 order-1 md:order-2">
              <div class="relative p-8 md:p-10 rounded-2xl" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(91,62,158,0.15);">
                <div class="flex items-center gap-3 mb-5">
                  <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background: linear-gradient(135deg, #5b3e9e, #7c5cbf);">
                    <svg class="w-5 h-5" fill="none" stroke="#fef9ef" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                  </div>
                </div>
                <h3 class="mb-4" style="font-family: 'Playfair Display', serif; color: #fef9ef; font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2;">
                  Park<br><span style="color: #d4a843; font-style: italic;">Experiences</span>
                </h3>
                <p style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.65); font-size: 0.95rem; line-height: 1.8;">
                  Honest, detailed reviews of rides, restaurants, and resort experiences through the lens of accessibility. No sugarcoating. No sponsored fluff. Just what your family actually needs to know before you go.
                </p>
                <!-- Tags -->
                <div class="mt-6 flex flex-wrap gap-2">
                  <span class="text-xs px-3 py-1.5 rounded-full" style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.6); border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.03);">Ride Reviews</span>
                  <span class="text-xs px-3 py-1.5 rounded-full" style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.6); border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.03);">Dining</span>
                  <span class="text-xs px-3 py-1.5 rounded-full" style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.6); border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.03);">Sensory Tips</span>
                  <span class="text-xs px-3 py-1.5 rounded-full" style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.6); border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.03);">Resorts</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TOPIC 03 — Family Disney Life (full-width editorial) -->
        <div class="relative">
          <!-- Oversized number -->
          <div class="absolute -top-8 md:-top-16 left-1/2 -translate-x-1/2 select-none pointer-events-none text-center" style="font-family: 'Playfair Display', serif; font-size: clamp(8rem, 18vw, 14rem); font-weight: 900; line-height: 1; color: rgba(212,168,67,0.05);">03</div>

          <div class="relative max-w-4xl mx-auto">
            <!-- Full-width editorial card -->
            <div class="relative rounded-3xl overflow-hidden" style="background: linear-gradient(160deg, rgba(91,62,158,0.15), rgba(212,168,67,0.08)); border: 1px solid rgba(212,168,67,0.1);">
              <!-- Inner layout -->
              <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Left: typographic art -->
                <div class="relative flex items-center justify-center p-10 md:p-14" style="min-height: 300px;">
                  <div class="absolute inset-0" style="background: radial-gradient(circle at 30% 50%, rgba(212,168,67,0.08), transparent 70%);"></div>
                  <div class="relative text-center">
                    <div class="flex items-center justify-center gap-3 mb-4">
                      <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background: linear-gradient(135deg, #d4a843, #5b3e9e);">
                        <svg class="w-5 h-5" fill="none" stroke="#fef9ef" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                      </div>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; color: #fef9ef; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; line-height: 1.15;">
                      Family<br>Disney<br><span style="color: #d4a843; font-style: italic;">Life</span>
                    </h3>
                    <!-- Decorative sparkles -->
                    <div class="mt-4 flex items-center justify-center gap-3">
                      <svg class="w-3 h-3" style="color: #d4a843; opacity: 0.4;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                      <svg class="w-4 h-4" style="color: #d4a843; opacity: 0.6;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                      <svg class="w-3 h-3" style="color: #d4a843; opacity: 0.4;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                  </div>
                </div>

                <!-- Right: content -->
                <div class="relative p-10 md:p-14 flex flex-col justify-center" style="border-left: 1px solid rgba(254,249,239,0.05);">
                  <p class="mb-6" style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.65); font-size: 0.95rem; line-height: 1.8;">
                    The sensory meltdowns in line. The pure joy on Viola's face during the fireworks. The snack strategy that saves every visit. This is the real, unfiltered, weekly Disney life of a family that just keeps going back.
                  </p>
                  <p style="font-family: 'Poppins', sans-serif; color: rgba(254,249,239,0.65); font-size: 0.95rem; line-height: 1.8;">
                    We share the chaos, the joy, the tips you only learn after hundreds of visits, and the moments that remind us why we do this.
                  </p>
                  <!-- Signature-style closing -->
                  <div class="mt-8 pt-6" style="border-top: 1px solid rgba(254,249,239,0.08);">
                    <p style="font-family: 'Playfair Display', serif; color: #d4a843; font-size: 1.1rem; font-style: italic;">
                      Funny. Messy. Magical. Weekly.
                    </p>
                  </div>
                </div>
              </div>
            </div>
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
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-center">
                    {{-- Photo area --}}
                    <div class="md:col-span-5 relative">
                        <div class="relative rounded-2xl overflow-hidden" style="aspect-ratio: 3/4;">
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, #1a1040, #5b3e9e);"></div>
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
                        <div class="hidden md:block absolute -bottom-4 -right-4 w-full h-full rounded-2xl -z-10" style="border: 1px solid rgba(212,168,67,0.15);"></div>
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-7">
                        <h3 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-2">Jeffrey Davidson</h3>
                        <p class="font-body text-sm font-semibold mb-6" style="color: #5b3e9e;">Dad, Software Engineer, Disney Strategist</p>
                        <div class="space-y-4">
                            <p class="text-navy/65 font-body leading-relaxed">The planner. The podcast editor. The guy who can navigate World Showcase blindfolded and has strong opinions about which Dole Whip flavor is the best (it's pineapple, don't argue).</p>
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
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-center">
                    {{-- Bio (left on desktop) --}}
                    <div class="md:col-span-7 order-2 md:order-1">
                        <h3 class="font-heading text-2xl md:text-3xl font-bold text-navy mb-2">Cassie Davidson</h3>
                        <p class="font-body text-sm font-semibold mb-6" style="color: #5b3e9e;">Mom, Baker, Accessibility Champion</p>
                        <div class="space-y-4">
                            <p class="text-navy/65 font-body leading-relaxed">The heart of Mouse28. Cassie is the one who makes sure every tip we share actually helps real families. She brings warmth, honesty, and a park snack bag that has never once let us down.</p>
                            <p class="text-navy/65 font-body leading-relaxed">When she's not at the parks, Cassie runs a cottage food bakery, wrangles two huskies, and somehow keeps everything running smoothly. She's the reason this whole thing works.</p>
                        </div>
                        {{-- Fun facts --}}
                        <div class="mt-8 flex flex-wrap gap-3">
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(91,62,158,0.1); border: 1px solid rgba(91,62,158,0.15);">Favorite park: Magic Kingdom</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(212,168,67,0.15); border: 1px solid rgba(212,168,67,0.2);">Cottage food baker</span>
                            <span class="text-xs px-4 py-2 rounded-full font-body font-medium" style="color: #1a1040; background: rgba(91,62,158,0.1); border: 1px solid rgba(91,62,158,0.15);">Rock Chalk Jayhawk</span>
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

    {{-- CTA --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden py-16 md:py-24">
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
