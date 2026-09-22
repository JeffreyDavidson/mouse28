<x-filament-widgets::widget>
    <div class="@container border-mouse-navy/15 border-b pb-8">
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
            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\Posts\PostResource::getUrl('create')"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedPlus"
                class="min-h-12"
            >
                New Post
            </x-filament::button>
            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\Episodes\EpisodeResource::getUrl('create')"
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedMicrophone"
                class="min-h-12"
            >
                New Episode
            </x-filament::button>
            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\Guides\GuideResource::getUrl('create')"
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedBookOpen"
                class="min-h-12"
            >
                New Guide
            </x-filament::button>
        </div>
    </div>
</x-filament-widgets::widget>
