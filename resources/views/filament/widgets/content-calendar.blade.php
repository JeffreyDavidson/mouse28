<x-filament-widgets::widget>
    <x-filament::section heading="Content Calendar — Next 7 Days" icon="heroicon-o-calendar-days">
        @php
            $items = $this->getTimeline();
        @endphp

        @if(count($items) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">No content scheduled for the next 7 days.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($items as $item)
                    <a href="{{ $item['url'] }}" class="flex items-center gap-4 py-3 px-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        {{-- Date --}}
                        <div class="flex-shrink-0 text-center w-14">
                            <div class="text-xs font-medium text-gray-400 uppercase">{{ \Carbon\Carbon::parse($item['date'])->format('M') }}</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white leading-tight">{{ \Carbon\Carbon::parse($item['date'])->format('j') }}</div>
                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($item['date'])->format('g:ia') }}</div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['title'] }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                {{-- Type badge --}}
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                                    'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400' => $item['type'] === 'Post',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' => $item['type'] === 'Episode',
                                ])>{{ $item['type'] }}</span>

                                {{-- Status badge --}}
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                                    'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' => $item['status'] === 'Published',
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400' => $item['status'] === 'Scheduled',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-500/20 dark:text-gray-400' => $item['status'] === 'Draft',
                                ])>{{ $item['status'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
