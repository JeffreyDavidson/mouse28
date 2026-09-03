@props([
    'compact' => false,
])

<span
    {{
        $attributes->class([
            'mouse28-wordmark',
            'mouse28-wordmark-compact' => $compact,
        ])
    }}
    data-brand-wordmark
>
    <span class="mouse28-wordmark-name">Mouse</span><span class="mouse28-wordmark-number">28</span>
    <span class="mouse28-wordmark-route" aria-hidden="true"></span>
</span>
