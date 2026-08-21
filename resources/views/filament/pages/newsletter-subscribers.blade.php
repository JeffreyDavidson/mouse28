<x-filament-panels::page>
    @php
        $subscribers = $this->getSubscribers();
        $error = $this->getErrorMessage();
        $count = count($subscribers);
    @endphp

    <x-filament.page-header
        title="Newsletter Subscribers"
        subtitle="Powered by Resend · Cached 5 min"
        class="mb-6"
    >
        <x-slot:icon>
            <svg class="size-8 text-mouse-gold-light" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
        </x-slot:icon>

        <x-slot:stats>
            <x-filament.resource-stat :label="str('subscriber')->plural($count)" tone="gold">{{ $count }}</x-filament.resource-stat>
        </x-slot:stats>

        @if ($count > 0)
            <x-slot:actions>
                <x-filament::button wire:click="exportCsv" color="warning" icon="heroicon-m-arrow-down-tray" class="min-h-12">
                    Export CSV
                </x-filament::button>
            </x-slot:actions>
        @endif
    </x-filament.page-header>

    @if ($error)
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-600/30 bg-red-600/10 px-6 py-5 text-sm text-red-300" role="alert">
            <svg class="mt-0.5 size-5 shrink-0 text-red-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
            <span class="font-mouse-body">{{ $error }}</span>
        </div>
    @endif

    @if ($count === 0 && ! $error)
        <div class="rounded-2xl border border-mouse-gold/12 bg-mouse-navy-light/30 px-6 py-16 text-center">
            <svg class="mx-auto mb-4 size-14 text-mouse-gold/25" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <h3 class="font-mouse-heading text-xl font-bold text-mouse-cream">No subscribers yet</h3>
            <p class="mt-2 font-mouse-body text-sm text-mouse-cream/50">Share your newsletter signup link to start growing your audience ✨</p>
        </div>
    @elseif ($count > 0)
        <div class="overflow-x-auto rounded-2xl border border-mouse-gold/12 bg-mouse-navy-light/30">
            <table class="w-full min-w-160 border-collapse">
                <thead class="bg-mouse-navy/60">
                    <tr>
                        <th class="border-b border-mouse-gold/10 px-6 py-4 text-left font-mouse-body text-xs font-semibold tracking-wider text-mouse-gold/80 uppercase" scope="col">#</th>
                        <th class="border-b border-mouse-gold/10 px-6 py-4 text-left font-mouse-body text-xs font-semibold tracking-wider text-mouse-gold/80 uppercase" scope="col">Email Address</th>
                        <th class="border-b border-mouse-gold/10 px-6 py-4 text-left font-mouse-body text-xs font-semibold tracking-wider text-mouse-gold/80 uppercase" scope="col">Subscribed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $index => $subscriber)
                        @php($subscribedAt = isset($subscriber['created_at']) ? \Carbon\Carbon::parse($subscriber['created_at']) : null)
                        <tr class="transition-colors hover:bg-mouse-gold/4">
                            <td class="border-b border-mouse-gold/6 px-6 py-4 font-mouse-body text-xs text-mouse-cream/40">{{ $index + 1 }}</td>
                            <td class="border-b border-mouse-gold/6 px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-mouse-purple to-mouse-navy-light font-mouse-body text-xs font-semibold text-mouse-gold-light" aria-hidden="true">
                                        {{ strtoupper(substr($subscriber['email'] ?? '?', 0, 1)) }}
                                    </span>
                                    <span class="font-mouse-body text-sm text-mouse-cream">{{ $subscriber['email'] ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="border-b border-mouse-gold/6 px-6 py-4 font-mouse-body text-sm text-mouse-cream/50">
                                @if ($subscribedAt)
                                    <time datetime="{{ $subscribedAt->toIso8601String() }}">
                                        {{ $subscribedAt->format('M j, Y') }}
                                        <span class="ml-2 text-xs text-mouse-cream/30">{{ $subscribedAt->format('g:ia') }}</span>
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
