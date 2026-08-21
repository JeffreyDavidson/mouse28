<x-filament.page-header
    title="Blog Posts"
    subtitle="Create and manage your blog content"
    icon-tone="purple"
    class="mb-6"
>
    <x-slot:icon>
        <svg class="text-mouse-gold-light size-8" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
            <path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/>
        </svg>
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="published">{{ $published }}</x-filament.resource-stat>

        @if ($drafts > 0)
            <x-filament.resource-stat :label="str('draft')->plural($drafts)" tone="gold">{{ $drafts }}</x-filament.resource-stat>
        @endif
    </x-slot:stats>

    <x-slot:actions>
        <x-filament::button tag="a" :href="$createUrl" color="warning" icon="heroicon-m-plus" class="min-h-12">
            New Post
        </x-filament::button>
    </x-slot:actions>
</x-filament.page-header>
