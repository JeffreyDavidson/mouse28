@props([
    'guide',
    'variant' => 'archive',
])

@if ($variant === 'related')
    <article class="group min-w-0">
        <a href="{{ route('guides.show', $guide) }}" class="block overflow-hidden rounded-xl">
            <x-guide-artwork
                :guide="$guide"
                class="aspect-[16/10] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
            />
        </a>
        <p class="text-purple mt-5 text-sm font-semibold">{{ $guide->category_label }}</p>
        <h3 class="font-heading group-hover:text-purple mt-2 text-2xl/[1.18] [font-weight:610] tracking-[-0.015em] text-balance transition-colors">
            <a href="{{ route('guides.show', $guide) }}">{{ $guide->title }}</a>
        </h3>
    </article>
@else
    <article class="group min-w-0">
        <a
            href="{{ route('guides.show', $guide) }}"
            aria-label="Read {{ $guide->title }}"
            class="block overflow-hidden rounded-xl"
        >
            <x-guide-artwork
                :guide="$guide"
                class="aspect-[16/10] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
            />
        </a>
        <div class="pt-5 wrap-anywhere">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <span class="text-purple font-semibold">{{ $guide->category_label }}</span>
                <span class="text-navy/60">{{ $guide->reading_time }} min read</span>
            </div>
            <h3 class="font-heading group-hover:text-purple mt-2 text-3xl/[1.15] [font-weight:610] tracking-[-0.02em] text-balance transition-colors">
                <a href="{{ route('guides.show', $guide) }}">{{ $guide->title }}</a>
            </h3>
            @if ($guide->excerpt)
                <p class="text-navy/70 mt-3 max-w-[62ch] text-base/7 text-pretty">
                    {{ Str::limit($guide->excerpt, 180) }}
                </p>
            @endif
            <a
                href="{{ route('guides.show', $guide) }}"
                class="text-purple decoration-gold/70 hover:text-navy mt-5 inline-flex min-h-12 items-center font-semibold underline underline-offset-8 transition-colors"
            >Read the guide</a>
        </div>
    </article>
@endif
