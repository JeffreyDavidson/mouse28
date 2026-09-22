<x-filament-panels::page>
    @php
        $audience = $this->getAudience();
        $subscribers = $audience['subscribers'];
        $error = $audience['error'];
        $count = count($subscribers);
        $subscribers = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($subscribers, ($this->getPage() - 1) * 50, 50),
            $count,
            50,
            $this->getPage(),
        );
    @endphp

    <x-filament.page-header
        title="Newsletter Subscribers"
        subtitle="Manage your newsletter audience · Resend · Cached 5 min"
        class="mb-6"
    >
        <x-slot:icon>
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelopeOpen"
                class="text-mouse-gold-light size-8"
                aria-hidden="true"
            />
        </x-slot:icon>

        <x-slot:stats>
            <x-filament.resource-stat label="Active" tone="gold">
                {{ $audience['active_count'] }}
            </x-filament.resource-stat>
            <x-filament.resource-stat label="Total contacts"> {{ $count }} </x-filament.resource-stat>
        </x-slot:stats>

        <x-slot:actions>
            <x-filament::button
                wire:click="refreshSubscribers"
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowPath"
                class="min-h-12"
            >
                Refresh
            </x-filament::button>
            @if ($count > 0)
                <x-filament::button
                    wire:click="exportCsv"
                    color="warning"
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowDownTray"
                    class="min-h-12"
                >
                    Export all contacts
                </x-filament::button>
            @endif
        </x-slot:actions>
    </x-filament.page-header>

    @if ($error)
        <x-filament.alert :icon="\Filament\Support\Icons\Heroicon::OutlinedExclamationTriangle">
            {{ $error }}
        </x-filament.alert>
    @endif

    @if ($count === 0 && ! $error)
        <x-filament.empty-state
            title="No contacts yet"
            description="Share your newsletter signup link to start growing your audience."
            :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelope"
        />
    @elseif ($count > 0)
        <div
            class="fi-ta-ctn focus-visible:outline-mouse-gold-light overflow-x-auto focus-visible:outline-2 focus-visible:outline-offset-2"
            role="region"
            aria-label="Newsletter contacts"
            tabindex="0"
        >
            <table class="fi-ta-table w-full min-w-160 border-collapse">
                <thead class="bg-mouse-cream-dark">
                    <tr>
                        <th
                            class="border-mouse-navy/10 font-mouse-body text-mouse-navy border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            #
                        </th>
                        <th
                            class="border-mouse-navy/10 font-mouse-body text-mouse-navy border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            Email Address
                        </th>
                        <th
                            class="border-mouse-navy/10 font-mouse-body text-mouse-navy border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            Created
                        </th>
                        <th
                            class="border-mouse-navy/10 font-mouse-body text-mouse-navy border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $index => $subscriber)
                        @php($createdAt = isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at']) : null)
                        <tr class="hover:bg-mouse-gold/4 transition-colors">
                            <td class="border-mouse-navy/10 font-mouse-body text-mouse-navy/75 border-b px-6 py-4 text-xs">
                                {{ $subscribers->firstItem() + $index }}
                            </td>
                            <td class="border-mouse-navy/10 border-b px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="font-mouse-body from-mouse-purple text-mouse-gold-light to-mouse-navy-light flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-br text-xs font-semibold"
                                        aria-hidden="true"
                                    >
                                        {{ strtoupper(substr($subscriber['email'] ?? '?', 0, 1)) }}
                                    </span>
                                    <span class="font-mouse-body text-mouse-navy text-sm">{{ $subscriber['email'] ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="border-mouse-navy/10 font-mouse-body text-mouse-navy/75 border-b px-6 py-4 text-sm">
                                @if ($createdAt)
                                    <time datetime="{{ $createdAt->toIso8601String() }}">
                                        {{ $createdAt->format('M j, Y') }}
                                        <span class="text-mouse-navy/75 ml-2 text-xs">{{ $createdAt->format('g:ia') }}</span>
                                    </time>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="border-mouse-navy/10 font-mouse-body text-mouse-navy border-b px-6 py-4 text-sm">
                                <x-filament::badge
                                    :color="match ($subscriber['subscription_status']) {
                                        'Subscribed' => 'success',
                                        'Unsubscribed' => 'danger',
                                        default => 'gray',
                                    }"
                                >
                                    {{ $subscriber['subscription_status'] }}
                                </x-filament::badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-mouse-navy/10 px-6 py-4">{{ $subscribers->links() }}</div>
        </div>
    @endif
</x-filament-panels::page>
