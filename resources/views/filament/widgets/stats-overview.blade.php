<x-filament-widgets::widget>
    <x-filament.dashboard-panel
        heading="At a glance"
        description="A quick view of your published content and audience."
    >
        <dl class="grid grid-cols-2 gap-x-6 gap-y-5 @5xl:grid-cols-5">
            @foreach ($this->getStats() as $stat)
                <div class="border-mouse-navy/15 min-w-0 border-b pb-5">
                    <dt class="text-mouse-navy/75 flex items-center gap-2 truncate text-base font-medium @lg:text-sm">
                        <x-filament::icon
                            :icon="$stat['icon']"
                            @class([
                                'size-5 shrink-0',
                                'text-mouse-purple' => $stat['color'] === 'purple',
                                'text-mouse-purple-light' => $stat['color'] === 'purple-light',
                                'text-mouse-gold-dark' => $stat['color'] === 'gold',
                                'text-mouse-teal' => $stat['color'] === 'teal',
                            ])
                            aria-hidden="true"
                        />
                        <span>{{ $stat['label'] }}</span>
                    </dt>
                    <dd @class([
                        'mt-2 text-3xl font-semibold tabular-nums',
                        'text-mouse-purple' => $stat['color'] === 'purple',
                        'text-mouse-purple-light' => $stat['color'] === 'purple-light',
                        'text-mouse-gold-dark' => $stat['color'] === 'gold',
                        'text-mouse-teal' => $stat['color'] === 'teal',
                    ])>
                        {{ $stat['description'] === 'Unavailable' ? '—' : $stat['value'] }}
                    </dd>
                    <dd class="text-mouse-navy/75 mt-1 text-sm">{{ $stat['description'] }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
