@props(['image', 'alt', 'name', 'role', 'facts'])

<article {{ $attributes->class(['border-gold/40 py-10 lg:py-14']) }}>
    <img
        src="{{ $image }}"
        alt="{{ $alt }}"
        width="2048"
        height="2048"
        loading="lazy"
        decoding="async"
        class="aspect-[5/4] w-full rounded-xl object-cover object-center shadow-[0_1.5rem_3.5rem_rgb(26_16_64/0.14)]"
    />
    <h3 class="font-heading text-navy mt-8 text-4xl [font-weight:620] tracking-[-0.02em]">{{ $name }}</h3>
    <p class="text-purple mt-2 font-semibold">{{ $role }}</p>
    <div class="text-navy/70 mt-6 max-w-[62ch] space-y-4 text-base/7 text-pretty">{{ $slot }}</div>
    <dl class="border-navy/15 mt-7 grid gap-5 border-t pt-5 sm:grid-cols-2">
        @foreach ($facts as $label => $value)
            <div>
                <dt class="text-navy/55 text-sm">{{ $label }}</dt>
                <dd class="text-navy mt-1 font-semibold">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
</article>
