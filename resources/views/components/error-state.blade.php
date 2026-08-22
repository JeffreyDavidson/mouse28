@props([
    'code',
    'eyebrow',
    'title',
    'message',
])

<section class="from-navy via-navy-light to-purple relative isolate flex min-h-[70vh] items-center overflow-hidden bg-linear-to-br py-16 text-white">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <span class="text-gold/25 absolute top-[12%] left-[12%] text-2xl">✦</span>
        <span class="text-gold/20 absolute top-[28%] right-[16%] text-base">✦</span>
        <span class="text-purple-light/30 absolute bottom-[18%] left-[22%] text-lg">✦</span>
        <div class="bg-gold/5 absolute top-1/2 left-1/2 size-[34rem] -translate-1/2 rounded-full blur-3xl"></div>
    </div>

    <div class="relative mx-auto w-full max-w-4xl px-4 text-center sm:px-6">
        <p class="font-heading text-gold/20 text-8xl font-bold sm:text-9xl" aria-hidden="true">{{ $code }}</p>
        <p class="text-gold mt-3 text-sm font-semibold tracking-[0.18em] uppercase">{{ $eyebrow }}</p>
        <h1 class="font-heading mt-4 text-4xl/tight font-bold sm:text-5xl md:text-6xl">{{ $title }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-base/relaxed text-white/65 sm:text-lg/relaxed">{{ $message }}</p>

        {{ $slot }}
    </div>
</section>
