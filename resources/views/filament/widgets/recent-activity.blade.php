<x-filament-widgets::widget>
    <div style="
        background: rgba(45, 27, 105, 0.3);
        border-radius: 1.25rem;
        border: 1px solid rgba(212, 168, 67, 0.12);
        overflow: hidden;
    ">
        <div style="
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(212, 168, 67, 0.1);
        ">
            <h3 style="
                font-family: 'Playfair Display', serif;
                font-size: 1.1rem;
                font-weight: 700;
                color: #f0c75e;
                margin: 0;
            ">Recent Activity</h3>
        </div>

        @php $activity = $this->getActivity(); @endphp

        @if (count($activity) > 0)
            <div style="padding: 0.5rem 0;">
                @foreach ($activity as $item)
                    <a href="{{ $item['url'] }}" style="
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        padding: 0.85rem 1.5rem;
                        text-decoration: none;
                        transition: background 0.2s ease;
                    " onmouseover="this.style.background='rgba(212, 168, 67, 0.06)'" onmouseout="this.style.background='transparent'">
                        <div style="
                            width: 36px;
                            height: 36px;
                            border-radius: 0.625rem;
                            background: {{ $item['color'] }}15;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        ">
                            <x-filament::icon
                                icon="heroicon-o-{{ $item['icon'] }}"
                                style="width: 18px; height: 18px; color: {{ $item['color'] }};"
                            />
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="
                                font-family: 'Poppins', sans-serif;
                                font-size: 0.85rem;
                                font-weight: 500;
                                color: #fef9ef;
                                white-space: nowrap;
                                overflow: hidden;
                                text-overflow: ellipsis;
                            ">{{ $item['label'] }}</div>
                            <div style="
                                font-family: 'Poppins', sans-serif;
                                font-size: 0.75rem;
                                color: rgba(254, 249, 239, 0.5);
                            ">{{ $item['type'] }}</div>
                        </div>
                        <div style="
                            font-family: 'Poppins', sans-serif;
                            font-size: 0.75rem;
                            color: rgba(254, 249, 239, 0.5);
                            flex-shrink: 0;
                        ">
                            {{ \Carbon\Carbon::parse($item['time'])->diffForHumans(short: true) }}
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div style="
                padding: 3rem 1.5rem;
                text-align: center;
            ">
                <div style="color: rgba(212, 168, 67, 0.3); margin-bottom: 0.75rem;">
                    <x-filament::icon icon="heroicon-o-sparkles" style="width: 40px; height: 40px; margin: 0 auto;" />
                </div>
                <p style="
                    font-family: 'Poppins', sans-serif;
                    color: rgba(254, 249, 239, 0.5);
                    font-size: 0.85rem;
                    margin: 0;
                ">No activity yet. Start creating something magical!</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
