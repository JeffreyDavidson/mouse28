@props([
    'guide',
    'loading' => 'lazy',
    'fetchpriority' => null,
])

@php
    $categoryArtwork = [
        'accessibility' => '/images/guides/accessibility.webp',
        'park-strategy' => '/images/guides/park-strategy.webp',
        'food-reviews' => '/images/guides/food-reviews.webp',
        'family-planning' => '/images/guides/family-planning.webp',
    ];
    $artworkUrl = $guide->cover_image_url ?: ($categoryArtwork[$guide->category] ?? $categoryArtwork['park-strategy']);
@endphp

<img
    src="{{ $artworkUrl }}"
    alt=""
    aria-hidden="true"
    loading="{{ $loading }}"
    decoding="async"
    @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    data-guide-artwork
    {{ $attributes }}
/>
