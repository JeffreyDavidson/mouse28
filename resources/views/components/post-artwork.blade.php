@use('App\Presenters\PostPresenter')

@props([
    'post',
    'compact' => false,
    'priority' => false,
    'sizes' => '(min-width: 1376px) 644px, (min-width: 768px) calc(50vw - 44px), (min-width: 640px) calc(100vw - 48px), calc(100vw - 32px)',
])

@php
    $presenter = PostPresenter::from($post);
    $artworkStyle = $presenter->artworkStyle();
    $monogram = Str::of($post->title)
        ->replaceMatches('/[^\pL\pN\s]+/u', '')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => Str::substr($word, 0, 1))
        ->implode('');
@endphp

@if ($post->featured_image_url)
    <img
        src="{{ $post->featured_image_url }}"
        @if ($srcset = $presenter->featuredImageSrcset())
            srcset="{{ $srcset }}"
            sizes="{{ ($priority ? '' : 'auto, ').$sizes }}"
        @endif
        alt=""
        width="1024"
        height="768"
        loading="{{ $priority ? 'eager' : 'lazy' }}"
        decoding="async"
        @if ($priority) fetchpriority="high" @endif
        {{ $attributes }}
    />
@else
    <div
        data-post-artwork-fallback
        aria-hidden="true"
        {{ $attributes->class(['relative isolate flex flex-col justify-between overflow-hidden bg-linear-to-br p-5', $artworkStyle['wash'], $artworkStyle['ink']]) }}
    >
        <span class="absolute -top-8 -right-6 size-28 rounded-full border border-current/15"></span>
        <span class="absolute right-5 bottom-5 size-12 rotate-6 rounded-lg border-2 border-current/15"></span>
        <span class="w-fit border-b border-current/30 pb-1 text-[0.625rem] font-bold tracking-[0.18em] uppercase">
            {{ $artworkStyle['stamp'] }}
        </span>
        @if ($compact)
            <span class="font-heading relative text-4xl [font-weight:650] tracking-[-0.04em] uppercase">{{ $monogram }}</span>
        @else
            <span class="font-heading relative line-clamp-4 max-w-[16ch] text-xl/6 [font-weight:650] tracking-[-0.02em] text-balance sm:text-2xl/7">
                {{ $post->title }}
            </span>
        @endif
        <span class="text-[0.625rem] font-semibold tracking-[0.14em] uppercase opacity-65">{{ $post->category_label }}</span>
    </div>
@endif
