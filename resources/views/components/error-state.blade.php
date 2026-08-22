@props([
    'code',
    'eyebrow',
    'title',
    'message',
])

<section class="relative isolate flex min-h-[70vh] items-center overflow-hidden bg-linear-to-br from-navy via-navy-light to-purple py-16 text-white">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <span class="absolute top-[12%] left-[12%] text-2xl text-gold/25">✦</span>
        <span class="absolute top-[28%] right-[16%] text-base text-gold/20">✦</span>
        <span class="absolute bottom-[18%] left-[22%] text-lg text-purple-light/30">✦</span>
        <div class="absolute top-1/2 left-1/2 size-[34rem] -translate-1/2 rounded-full bg-gold/5 blur-3xl"></div>
    </div>

    <div class="relative mx-auto w-full max-w-4xl px-4 text-center sm:px-6">
        <p class="font-heading text-8xl font-bold text-gold/20 sm:text-9xl" aria-hidden="true">{{ $code }}</p>
        <p class="mt-3 text-sm font-semibold tracking-[0.18em] text-gold uppercase">{{ $eyebrow }}</p>
        <h1 class="mt-4 font-heading text-4xl/tight font-bold sm:text-5xl md:text-6xl">{{ $title }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-base/relaxed text-white/65 sm:text-lg/relaxed">{{ $message }}</p>

        {{ $slot }}
    </div>
</section>
