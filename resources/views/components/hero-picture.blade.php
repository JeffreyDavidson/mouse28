{{--
    The Kilimanjaro Safaris family photo as AVIF and WebP sources. Each page passes its
    layout's sizes and its own fallback image (src, alt, width, height, class).
--}}
@props(['sizes'])

<picture>
    <source
        type="image/avif"
        srcset="/images/hero-family-640.avif 640w, /images/hero-family-768.avif 768w, /images/hero-family-1024.avif 1024w, /images/hero-family-1600.avif 1600w"
        sizes="{{ $sizes }}"
    />
    <source
        type="image/webp"
        srcset="/images/hero-family-640.webp 640w, /images/hero-family-768.webp 768w, /images/hero-family-1024.webp 1024w, /images/hero-family.webp 1600w"
        sizes="{{ $sizes }}"
    />
    <img {{ $attributes }} fetchpriority="high" />
</picture>
