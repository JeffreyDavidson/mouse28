<x-filament-panels::page>
    <div class="flex justify-end mb-4">
        <x-filament::button wire:click="exportCsv" icon="heroicon-o-arrow-down-tray">
            Export CSV
        </x-filament::button>
    </div>

    @php
        $subscribers = $this->getSubscribers();
    @endphp

    @if(count($subscribers) === 0)
        <div class="text-center py-12">
            <x-heroicon-o-envelope-open class="w-12 h-12 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No subscribers found</h3>
            <p class="text-sm text-gray-500 mt-1">Either there are no subscribers yet, or the API could not be reached.</p>
        </div>
    @else
        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead>
                    <tr>
                        <th class="fi-ta-header-cell px-4 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Email</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">Subscribed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach($subscribers as $subscriber)
                        <tr>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $subscriber['email'] ?? '—' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at'])->format('M j, Y g:ia') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ count($subscribers) }} subscriber(s) · Data cached for 5 minutes</p>
    @endif
</x-filament-panels::page>
