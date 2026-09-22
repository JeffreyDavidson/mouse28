@props([
    'tone' => 'danger',
    'icon' => null,
])

<div
    {{
        $attributes->class([
            'mb-6 flex items-start gap-3 rounded-2xl border px-6 py-5 text-sm',
            'border-danger-600/30 bg-danger-50 text-danger-800' => $tone === 'danger',
            'border-warning-600/30 bg-warning-50 text-warning-800' => $tone === 'warning',
            'border-success-600/30 bg-success-50 text-success-800' => $tone === 'success',
            'border-primary-600/30 bg-primary-50 text-primary-800' => $tone === 'primary',
        ])
    }}
    role="alert"
>
    @if ($icon)
        <x-filament::icon :icon="$icon" class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
    @endif

    <div class="font-mouse-body">{{ $slot }}</div>
</div>
