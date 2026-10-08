@use('App\Presenters\GuidePresenter')

@props([
    'guide',
    'loading' => 'lazy',
    'fetchpriority' => null,
    'sizes' => '100vw',
])

@php
    $presenter = GuidePresenter::from($guide);
    $artworkUrl = $presenter->artworkUrl();
    $srcset = $presenter->artworkSrcset();
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
