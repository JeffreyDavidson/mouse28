<x-filament-widgets::widget>
    <div class="border-mouse-gold/20 from-mouse-navy to-mouse-purple via-mouse-navy-light relative overflow-hidden rounded-[1.25rem] border bg-linear-to-br p-6 sm:px-10 sm:py-8">
        <div class="pointer-events-none absolute top-[-30%] right-[-10%] size-75 bg-[radial-gradient(circle,rgb(212_168_67/15%)_0%,transparent_70%)]"></div>
        <span
            class="text-mouse-gold-light/30 pointer-events-none absolute top-[15%] right-[15%] text-xs"
            aria-hidden="true"
        >✦</span>
        <span
            class="text-mouse-gold-light/20 pointer-events-none absolute top-[60%] right-[25%] text-[0.6rem]"
            aria-hidden="true"
        >✦</span>
        <span
            class="text-mouse-gold-light/25 pointer-events-none absolute right-[10%] bottom-[20%] text-base"
            aria-hidden="true"
        >✦</span>

        <div class="relative z-1 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-mouse-heading text-mouse-gold-light mb-2 text-2xl font-bold sm:text-[1.75rem]">
                    Welcome back, {{ auth()->user()->name }} <span aria-hidden="true">✨</span>
                </h2>
                <p class="font-mouse-body text-mouse-cream/70 text-base sm:text-sm">
                    @php
                        $hour = now('America/New_York')->hour;
                        $greeting = match (true) {
                            $hour < 12 => 'Good morning!',
                            $hour < 17 => 'Good afternoon!',
                            default => 'Good evening!',
                        };
                        $postCount = \App\Models\Post::where('is_published', false)->count();
                        $guideCount = \App\Models\Guide::where('is_published', false)->count();
                    @endphp
                    {{ $greeting }}
                    @if ($postCount > 0 || $guideCount > 0)
                        You have
                        @if ($postCount > 0) {{ $postCount }}draft{{ str('post')->plural($postCount) }} @endif
                        @if ($postCount > 0 && $guideCount > 0) and @endif
                        @if ($guideCount > 0) {{ $guideCount }}draft{{ str('guide')->plural($guideCount) }} @endif
                    @else
                        Ready to create something magical?
                    @endif
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a
                    href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}"
                    class="font-mouse-body from-mouse-gold hover:from-mouse-gold-light hover:to-mouse-gold text-mouse-navy to-mouse-gold-dark inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-linear-to-br px-5 py-2.5 text-base font-semibold no-underline transition hover:-translate-y-0.5 sm:text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                    New Post
                </a>
                <a
                    href="{{ \App\Filament\Resources\Guides\GuideResource::getUrl('create') }}"
                    class="bg-mouse-cream/10 border-mouse-gold/30 font-mouse-body hover:bg-mouse-cream/15 text-mouse-cream inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-base font-medium no-underline transition hover:-translate-y-0.5 sm:text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                    </svg>
                    New Guide
                </a>
                <a
                    href="{{ \App\Filament\Resources\Episodes\EpisodeResource::getUrl('create') }}"
                    class="bg-mouse-cream/10 border-mouse-gold/30 font-mouse-body hover:bg-mouse-cream/15 text-mouse-cream inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-base font-medium no-underline transition hover:-translate-y-0.5 sm:text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" x2="12" y1="19" y2="22" />
                    </svg>
                    New Episode
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
