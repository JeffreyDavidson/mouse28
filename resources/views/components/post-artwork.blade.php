@props([
    'post',
    'compact' => false,
])

@php
    $artworkStyles = [
        'disney-tips' => ['wash' => 'from-gold/35 via-cream to-purple/15', 'ink' => 'text-navy', 'stamp' => 'Planning note'],
        'park-accessibility' => ['wash' => 'from-purple/30 via-cream to-gold/20', 'ink' => 'text-purple-dark', 'stamp' => 'Access field note'],
        'episode-recap' => ['wash' => 'from-emerald-800/25 via-cream to-gold/20', 'ink' => 'text-emerald-900', 'stamp' => 'From the podcast'],
        'family-life' => ['wash' => 'from-blue-800/25 via-cream to-gold/25', 'ink' => 'text-blue-950', 'stamp' => 'Family dispatch'],
        'autism-awareness' => ['wash' => 'from-pink-800/20 via-cream to-purple/20', 'ink' => 'text-pink-950', 'stamp' => 'Different perspectives'],
        'disney-news' => ['wash' => 'from-orange-700/25 via-cream to-purple/15', 'ink' => 'text-orange-950', 'stamp' => 'Park bulletin'],
        'food-reviews' => ['wash' => 'from-amber-800/25 via-cream to-gold/25', 'ink' => 'text-amber-950', 'stamp' => 'Table notes'],
        'resort-reviews' => ['wash' => 'from-teal-800/25 via-cream to-gold/20', 'ink' => 'text-teal-950', 'stamp' => 'Resort field note'],
        'disney-plus' => ['wash' => 'from-indigo-800/25 via-cream to-purple/20', 'ink' => 'text-indigo-950', 'stamp' => 'Watch list'],
        'merchandise' => ['wash' => 'from-rose-800/20 via-cream to-gold/25', 'ink' => 'text-rose-950', 'stamp' => 'Things we found'],
        'general' => ['wash' => 'from-cyan-900/20 via-cream to-gold/20', 'ink' => 'text-navy', 'stamp' => 'Mouse28 dispatch'],
    ];
    $artworkStyle = $artworkStyles[$post->category] ?? $artworkStyles['general'];
    $monogram = Str::of($post->title)
        ->replaceMatches('/[^\pL\pN\s]+/u', '')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => Str::substr($word, 0, 1))
        ->implode('');
@endphp

@if ($post->cover_image_url)
    <img
        src="{{ $post->cover_image_url }}"
        alt=""
        width="1024"
        height="768"
        loading="lazy"
        decoding="async"
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
