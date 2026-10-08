<x-filament-widgets::widget>
    <x-filament.dashboard-panel content-class="mt-0" class="p-6 sm:p-8">
        <div class="flex flex-col gap-6 @3xl:flex-row @3xl:items-end @3xl:justify-between">
            <div class="min-w-0">
                <h2 class="font-mouse-heading text-mouse-navy text-3xl font-semibold tracking-tight text-balance">
                    Welcome back, {{ auth()->user()->name }}
                </h2>
                <p class="text-mouse-navy/75 mt-3 max-w-prose text-base text-pretty">
                    Your stories, ready for their next chapter.
                </p>
            </div>
            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="text-mouse-purple inline-flex min-h-12 shrink-0 items-center gap-2 text-sm font-medium underline-offset-4 hover:underline"
            >
                Visit site
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowUpRight" class="size-4" />
                <span class="sr-only">(opens in a new tab)</span>
            </a>
        </div>
        <div class="mt-6 flex flex-col gap-3 @lg:flex-row @lg:flex-wrap">
            @foreach ($this->getCreateLinks() as $link)
                <x-filament::button
                    tag="a"
                    :href="$link['url']"
                    :color="$loop->first ? 'primary' : 'gray'"
                    :icon="$loop->first ? \Filament\Support\Icons\Heroicon::OutlinedPlus : $link['type']->getIcon()"
                    class="min-h-12"
                >
                    {{ $link['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
