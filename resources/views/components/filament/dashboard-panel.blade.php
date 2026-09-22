@props(['heading', 'description' => null])

<section {{ $attributes->class(['@container h-full rounded-2xl border border-mouse-navy/12 bg-white p-5']) }}>
    <h2 class="font-mouse-heading text-mouse-navy text-xl font-semibold text-balance">{{ $heading }}</h2>
    @if ($description)
        <p class="text-mouse-navy/75 mt-2 text-base text-pretty @lg:text-sm">{{ $description }}</p>
    @endif
    <div class="mt-6">{{ $slot }}</div>
</section>
