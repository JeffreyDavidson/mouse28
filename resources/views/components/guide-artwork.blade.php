@props([
    'guide',
    'loading' => 'lazy',
    'fetchpriority' => null,
    'sizes' => '100vw',
])

@inject('responsiveImages', 'App\Services\ResponsiveImageVariants')

@php
    $categoryArtwork = [
        'accessibility' => '/images/guides/accessibility.webp',
        'park-strategy' => '/images/guides/park-strategy.webp',
        'food-reviews' => '/images/guides/food-reviews.webp',
        'family-planning' => '/images/guides/family-planning.webp',
    ];
    $artworkUrl = $guide->featured_image_url ?: ($categoryArtwork[$guide->category?->value] ?? $categoryArtwork['park-strategy']);
    $srcset = $guide->featured_image_url ? $responsiveImages->srcset($guide->featured_image_path) : null;
@endphp

<img
    src="{{ $artworkUrl }}"
    @if ($srcset)
        srcset="{{ $srcset }}"
        sizes="{{ ($loading === 'lazy' ? 'auto, ' : '').$sizes }}"
    @endif
    alt=""
    aria-hidden="true"
    loading="{{ $loading }}"
    decoding="async"
    @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    data-guide-artwork
    {{ $attributes }}
/>
