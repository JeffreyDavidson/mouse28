@props(['message' => 'Preview mode. This page is only visible to administrators.'])

<div {{ $attributes->class(['bg-gold text-navy px-4 py-3 text-center text-sm font-semibold']) }} role="status">
    {{ $message }}
</div>
