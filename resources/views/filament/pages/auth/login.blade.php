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
    {{-- Logo --}}
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 800; color: #f0c75e; letter-spacing: -0.02em;">
            Mouse28
        </div>
        <div style="font-family: 'Poppins', sans-serif; font-size: 0.8rem; color: rgba(254, 249, 239, 0.5); letter-spacing: 0.15em; text-transform: uppercase; margin-top: 0.25rem;">
            Where Every Visit Tells a Story
        </div>
    </div>

    {{ $this->content }}
</x-filament-panels::page.simple>
