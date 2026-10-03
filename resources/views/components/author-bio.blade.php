@props([
    'name',
    'headingId',
    'bio' => null,
])

<div {{ $attributes->class(['flex items-start gap-5']) }} data-author-bio>
    <span class="border-gold/35 text-gold-ink font-heading inline-flex size-14 shrink-0 items-center justify-center rounded-full border font-semibold">
        {{ Str::initials($name, capitalize: true) }}
    </span>
    <div>
        <h2 id="{{ $headingId }}" class="font-heading text-navy text-2xl [font-weight:620]">{{ $name }}</h2>
        <p class="text-navy/68 mt-2 text-sm/7">
            {{ filled($bio) ? $bio : 'Disney park explorer, accessibility advocate, and parent.' }}
        </p>
    </div>
</div>
