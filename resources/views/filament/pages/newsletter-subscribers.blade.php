<x-filament-panels::page>
    @php
        $audience = $this->getAudience();
        $subscribers = $audience['subscribers'];
        $error = $audience['error'];
        $count = count($subscribers);
        $perPage = $this->subscribersPerPage;
        $subscribers = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($subscribers, ($this->getPage() - 1) * $perPage, $perPage),
            $count,
            $perPage,
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
            class="fi-ta-ctn fi-ta-ctn-with-footer focus-visible:outline-mouse-gold-light overflow-hidden focus-visible:outline-2 focus-visible:outline-offset-2"
            role="region"
            aria-label="Newsletter contacts"
            tabindex="0"
        >
            <div class="fi-ta-main">
                <div
                    class="fi-ta-content-ctn fi-fixed-positioning-context overflow-x-auto"
                    tabindex="0"
                    aria-label="Newsletter contacts table"
                >
                    <div class="fi-ta-content">
                        <table class="fi-ta-table w-full min-w-160">
                            <thead>
                                <tr>
                                    <th class="fi-ta-header-cell" scope="col">#</th>
                                    <th class="fi-ta-header-cell" scope="col">Email Address</th>
                                    <th class="fi-ta-header-cell" scope="col">Created</th>
                                    <th class="fi-ta-header-cell" scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscribers as $index => $subscriber)
                                    @php($createdAt = isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at']) : null)
                                    <tr class="fi-ta-row">
                                        <td class="fi-ta-cell font-mouse-body text-mouse-navy/75 text-xs">
                                            {{ $subscribers->firstItem() + $index }}
                                        </td>
                                        <td class="fi-ta-cell">
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
                                        <td class="fi-ta-cell font-mouse-body text-mouse-navy/75 text-sm">
                                            @if ($createdAt)
                                                <time datetime="{{ $createdAt->toIso8601String() }}">
                                                    {{ $createdAt->format('M j, Y') }}
                                                    <span class="text-mouse-navy/75 ml-2 text-xs">{{ $createdAt->format('g:ia') }}</span>
                                                </time>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="fi-ta-cell font-mouse-body text-mouse-navy text-sm">
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
                    </div>
                </div>
                <x-filament::pagination
                    current-page-option-property="subscribersPerPage"
                    :page-options="\App\Filament\Pages\NewsletterSubscribers::SUBSCRIBERS_PER_PAGE_OPTIONS"
                    :paginator="$subscribers"
                />
            </div>
        </div>
    @endif
</x-filament-panels::page>
