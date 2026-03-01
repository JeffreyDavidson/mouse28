{{-- Floating stars --}}
@for ($i = 0; $i < 30; $i++)
    <div class="star" style="
        top: {{ rand(5, 90) }}%;
        left: {{ rand(5, 95) }}%;
        width: {{ rand(2, 4) }}px;
        height: {{ rand(2, 4) }}px;
        --duration: {{ rand(20, 50) / 10 }}s;
        --delay: {{ rand(0, 30) / 10 }}s;
    "></div>
@endfor

{{-- Castle silhouette --}}
<div class="castle-silhouette"></div>

<x-filament-panels::page.simple>
    {{ $this->content }}
</x-filament-panels::page.simple>
