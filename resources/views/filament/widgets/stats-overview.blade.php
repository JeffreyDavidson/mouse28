<x-filament-widgets::widget>
    @php $stats = $this->getStats(); @endphp
    <style>
        .m28-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }
        @media (max-width: 1024px) {
            .m28-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .m28-stats-grid { grid-template-columns: 1fr; }
        }
        .m28-stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .m28-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 168, 67, 0.15);
            border-color: rgba(212, 168, 67, 0.4) !important;
        }
    </style>
    <div class="m28-stats-grid">
        @foreach($stats as $stat)
            <div class="m28-stat-card" style="background: linear-gradient(135deg, rgba(26, 16, 64, 0.95), rgba(45, 27, 105, 0.9)); border: 1px solid rgba(212, 168, 67, 0.15); border-radius: 1.25rem; padding: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; background: radial-gradient(circle at top right, rgba(212, 168, 67, 0.08), transparent 70%); pointer-events: none;"></div>
                <div style="display: flex; align-items: flex-start; gap: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 0.75rem; background: {{ $stat['color'] }}22; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <x-filament::icon :icon="$stat['icon']" style="width: 24px; height: 24px; color: {{ $stat['color'] }};" />
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <p style="font-family: 'Poppins', sans-serif; font-size: 0.75rem; font-weight: 500; color: rgba(254, 249, 239, 0.6); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">{{ $stat['label'] }}</p>
                        <p style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #fef9ef; margin: 0.25rem 0; line-height: 1.1;">{{ $stat['value'] }}</p>
                        <p style="font-family: 'Poppins', sans-serif; font-size: 0.75rem; color: rgba(240, 199, 94, 0.7); margin: 0;">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
