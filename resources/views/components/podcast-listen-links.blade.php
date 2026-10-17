@props(['links', 'columns' => false])

{{-- The show's listening links as pill buttons, shared by the homepage and the podcast archive. With columns, they sit two to a row in equal widths. --}}
<nav
    aria-label="Listen to the Mouse28 podcast"
    {{ $attributes->class(['flex flex-wrap gap-3' => ! $columns, 'grid grid-cols-2 gap-2' => $columns]) }}
>
    @foreach ($links as $link)
        <a
            href="{{ $link['url'] }}"
            target="_blank"
            rel="noopener noreferrer"
            @class([
                'border-cream/35 text-cream hover:border-gold hover:text-gold inline-flex min-h-12 items-center justify-center rounded-full border py-2.5 text-sm font-semibold transition-colors',
                'px-5' => ! $columns,
                'px-3 whitespace-nowrap' => $columns,
            ])
            >{{ $link['label'] }}<x-new-tab-notice
        /></a>
    @endforeach
</nav>
