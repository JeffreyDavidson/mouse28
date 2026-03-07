<x-filament-widgets::widget>
    @php $items = $this->getTimeline(); @endphp
    <div style="background: linear-gradient(135deg, rgba(26, 16, 64, 0.95), rgba(45, 27, 105, 0.9)); border: 1px solid rgba(212, 168, 67, 0.15); border-radius: 1.25rem; padding: 1.5rem; position: relative; overflow: hidden; height: 100%;">
        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
            <span style="font-size: 1.1rem;">📅</span>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #fef9ef; margin: 0;">Upcoming Content</h3>
            <div style="flex: 1; height: 1px; background: linear-gradient(90deg, rgba(212, 168, 67, 0.3), transparent); margin-left: 0.5rem;"></div>
        </div>

        @if(count($items) === 0)
            {{-- Empty state --}}
            <div style="text-align: center; padding: 2rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">✨</div>
                <p style="font-family: 'Playfair Display', serif; font-size: 1rem; color: #fef9ef; margin: 0 0 0.5rem 0;">Your week is wide open!</p>
                <p style="font-family: 'Poppins', sans-serif; font-size: 0.8rem; color: rgba(240, 199, 94, 0.6); margin: 0;">Schedule some magical content for the next 7 days.</p>
            </div>
        @else
            {{-- Timeline --}}
            <div style="position: relative; padding-left: 1.5rem;">
                {{-- Vertical line --}}
                <div style="position: absolute; left: 5px; top: 4px; bottom: 4px; width: 2px; background: linear-gradient(180deg, rgba(212, 168, 67, 0.3), rgba(91, 62, 158, 0.3));"></div>

                @foreach($items as $i => $item)
                    @php
                        $dotColor = match($item['status']) {
                            'Published' => '#d4a843',
                            'Draft' => '#5b3e9e',
                            'Scheduled' => '#f0c75e',
                            default => '#5b3e9e',
                        };
                    @endphp
                    <a href="{{ $item['url'] }}" style="display: block; position: relative; padding-bottom: {{ $loop->last ? '0' : '1rem' }}; text-decoration: none;">
                        {{-- Dot --}}
                        <div style="position: absolute; left: -1.5rem; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: {{ $dotColor }}; border: 2px solid rgba(26, 16, 64, 0.8); box-shadow: 0 0 6px {{ $dotColor }}44;"></div>

                        <div style="padding: 0.5rem 0.75rem; border-radius: 0.75rem; transition: background 0.15s ease;">
                            <p style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; font-weight: 500; color: #fef9ef; margin: 0; line-height: 1.3;">{{ $item['title'] }}</p>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                                <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: rgba(240, 199, 94, 0.7);">{{ \Carbon\Carbon::parse($item['date'])->format('M j, g:ia') }}</span>
                                <span style="font-family: 'Poppins', sans-serif; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $dotColor }}; background: {{ $dotColor }}18; padding: 0.1rem 0.4rem; border-radius: 0.25rem;">{{ $item['status'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
