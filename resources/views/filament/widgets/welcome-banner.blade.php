<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-[1.25rem] border border-mouse-gold/20 bg-linear-to-br from-mouse-navy via-mouse-navy-light to-mouse-purple p-6 sm:px-10 sm:py-8">
        <div class="pointer-events-none absolute top-[-30%] right-[-10%] size-75 bg-[radial-gradient(circle,rgb(212_168_67/15%)_0%,transparent_70%)]"></div>
        <span class="pointer-events-none absolute top-[15%] right-[15%] text-xs text-mouse-gold-light/30" aria-hidden="true">✦</span>
        <span class="pointer-events-none absolute top-[60%] right-[25%] text-[0.6rem] text-mouse-gold-light/20" aria-hidden="true">✦</span>
        <span class="pointer-events-none absolute right-[10%] bottom-[20%] text-base text-mouse-gold-light/25" aria-hidden="true">✦</span>

        <div class="relative z-1 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="mb-2 font-mouse-heading text-2xl font-bold text-mouse-gold-light sm:text-[1.75rem]">
                    Welcome back, {{ auth()->user()->name }} ✨
                </h2>
                <p class="font-mouse-body text-base text-mouse-cream/70 sm:text-sm">
                    @php
                        $hour = now('America/New_York')->hour;
                        $greeting = match(true) {
                            $hour < 12 => 'Good morning!',
                            $hour < 17 => 'Good afternoon!',
                            default => 'Good evening!',
                        };
                        $postCount = \App\Models\Post::where('is_published', false)->count();
                        $storyCount = 0; // Community Stories disabled
                    @endphp
                    {{ $greeting }}
                    @if ($postCount > 0 || $storyCount > 0)
                        You have
                        @if ($postCount > 0) {{ $postCount }} draft {{ str('post')->plural($postCount) }} @endif
                        @if ($postCount > 0 && $storyCount > 0) and @endif
                        @if ($storyCount > 0) {{ $storyCount }} {{ str('story')->plural($storyCount) }} awaiting review @endif
                    @else
                        Ready to create something magical?
                    @endif
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-linear-to-br from-mouse-gold to-mouse-gold-dark px-5 py-2.5 font-mouse-body text-base font-semibold text-mouse-navy no-underline transition hover:-translate-y-0.5 hover:from-mouse-gold-light hover:to-mouse-gold sm:text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    New Post
                </a>
                <a href="{{ \App\Filament\Resources\Episodes\EpisodeResource::getUrl('create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-mouse-gold/30 bg-mouse-cream/10 px-5 py-2.5 font-mouse-body text-base font-medium text-mouse-cream no-underline transition hover:-translate-y-0.5 hover:bg-mouse-cream/15 sm:text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                    New Episode
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
