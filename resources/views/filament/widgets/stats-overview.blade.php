<x-filament-widgets::widget>
    <div class="@container">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-5 @5xl:grid-cols-5">
            @foreach ($this->getStats() as $stat)
                <div class="border-mouse-navy/15 min-w-0 border-b pb-5">
                    <dt class="text-mouse-navy/75 truncate text-base font-medium @lg:text-sm">{{ $stat['label'] }}</dt>
                    <dd class="text-mouse-navy mt-2 text-3xl font-semibold tabular-nums">
                        {{ $stat['description'] === 'Unavailable' ? '—' : $stat['value'] }}
                    </dd>
                    <dd class="text-mouse-navy/75 mt-1 text-sm">{{ $stat['description'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</x-filament-widgets::widget>
