<x-filament-panels::page>
    @php
        $audience = $this->getAudience();
        $subscribers = $audience['subscribers'];
        $error = $audience['error'];
        $count = count($subscribers);
    @endphp

    <x-filament.page-header title="Newsletter Subscribers" subtitle="Powered by Resend · Cached 5 min" class="mb-6">
        <x-slot:icon>
            <svg class="text-mouse-gold-light size-8" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
        </x-slot:icon>

        <x-slot:stats>
            <x-filament.resource-stat :label="str('subscriber')->plural($count)" tone="gold">
                {{ $count }}</x-filament.resource-stat>
        </x-slot:stats>

        <x-slot:actions>
            <x-filament::button
                wire:click="refreshSubscribers"
                color="gray"
                icon="heroicon-m-arrow-path"
                class="min-h-12"
            >
                Refresh
            </x-filament::button>
            @if ($count > 0)
                <x-filament::button
                    wire:click="exportCsv"
                    color="warning"
                    icon="heroicon-m-arrow-down-tray"
                    class="min-h-12"
                >
                    Export CSV
                </x-filament::button>
            @endif
        </x-slot:actions>
    </x-filament.page-header>

    @if ($error)
        <div
            class="mb-6 flex items-start gap-3 rounded-2xl border border-red-600/30 bg-red-600/10 px-6 py-5 text-sm text-red-300"
            role="alert"
        >
            <svg class="mt-0.5 size-5 shrink-0 text-red-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" />
            </svg>
            <span class="font-mouse-body">{{ $error }}</span>
        </div>
    @endif

    @if ($count === 0 && ! $error)
        <div class="bg-mouse-navy-light/30 border-mouse-gold/12 rounded-2xl border px-6 py-16 text-center">
            <svg class="text-mouse-gold/25 mx-auto mb-4 size-14" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
            <h3 class="font-mouse-heading text-mouse-cream text-xl font-bold">No subscribers yet</h3>
            <p class="font-mouse-body text-mouse-cream/50 mt-2 text-sm">
                Share your newsletter signup link to start growing your audience <span aria-hidden="true">✨</span>
            </p>
        </div>
    @elseif ($count > 0)
        <div class="bg-mouse-navy-light/30 border-mouse-gold/12 overflow-x-auto rounded-2xl border">
            <table class="w-full min-w-160 border-collapse">
                <thead class="bg-mouse-navy/60">
                    <tr>
                        <th
                            class="border-mouse-gold/10 font-mouse-body text-mouse-gold/80 border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            #
                        </th>
                        <th
                            class="border-mouse-gold/10 font-mouse-body text-mouse-gold/80 border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            Email Address
                        </th>
                        <th
                            class="border-mouse-gold/10 font-mouse-body text-mouse-gold/80 border-b px-6 py-4 text-left text-xs font-semibold tracking-wider uppercase"
                            scope="col"
                        >
                            Subscribed
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $index => $subscriber)
                        @php($subscribedAt = isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at']) : null)
                        <tr class="hover:bg-mouse-gold/4 transition-colors">
                            <td class="border-mouse-gold/6 font-mouse-body text-mouse-cream/40 border-b px-6 py-4 text-xs">
                                {{ $index + 1 }}
                            </td>
                            <td class="border-mouse-gold/6 border-b px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="font-mouse-body from-mouse-purple text-mouse-gold-light to-mouse-navy-light flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-br text-xs font-semibold"
                                        aria-hidden="true"
                                    >
                                        {{ strtoupper(substr($subscriber['email'] ?? '?', 0, 1)) }}
                                    </span>
                                    <span class="font-mouse-body text-mouse-cream text-sm">{{ $subscriber['email'] ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="border-mouse-gold/6 font-mouse-body text-mouse-cream/50 border-b px-6 py-4 text-sm">
                                @if ($subscribedAt)
                                    <time datetime="{{ $subscribedAt->toIso8601String() }}">
                                        {{ $subscribedAt->format('M j, Y') }}
                                        <span class="text-mouse-cream/30 ml-2 text-xs">{{ $subscribedAt->format('g:ia') }}</span>
                                    </time>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
