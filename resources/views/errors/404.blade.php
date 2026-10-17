<x-layouts.error
    title="Page Not Found | Mouse28"
    description="The page you requested could not be found. Search Mouse28 or continue exploring our Disney park stories and podcast."
    og-title="Page Not Found | Mouse28"
>
    <x-error-state
        code="404"
        title="That page wandered off"
        message="The address may have changed, or the page may no longer be available. Search Mouse28 or choose a place to keep exploring."
    >
        <form
            action="{{ route('search') }}"
            method="GET"
            role="search"
            class="flex max-w-xl flex-col gap-3 sm:flex-row"
        >
            <label for="not-found-search" class="sr-only">Search Mouse28</label>
            <input
                id="not-found-search"
                type="search"
                name="q"
                maxlength="100"
                placeholder="Search posts and episodes"
                class="text-navy placeholder:text-navy/60 focus:border-purple focus:ring-purple rounded-control min-h-12 min-w-0 flex-1 border border-white/15 bg-white px-5 py-3 text-base shadow-lg focus:ring-2 focus:outline-none"
            />
            <button
                type="submit"
                class="bg-gold text-navy hover:bg-gold-light rounded-control min-h-12 px-7 py-3 font-semibold shadow-lg transition-colors"
            >
                Search
            </button>
        </form>

        <div class="mt-6 flex flex-wrap gap-3">
            <a
                href="{{ route('home') }}"
                class="dispatch-error-secondary rounded-control inline-flex min-h-12 items-center px-6 py-3 font-semibold transition-colors"
            >Go home</a>
            <a
                href="{{ route('blog.index') }}"
                class="dispatch-error-secondary rounded-control inline-flex min-h-12 items-center px-6 py-3 font-semibold transition-colors"
            >Browse the blog</a>
            @if (config('mouse28.guides_enabled'))
                <a
                    href="{{ route('guides.index') }}"
                    class="dispatch-error-secondary rounded-control inline-flex min-h-12 items-center px-6 py-3 font-semibold transition-colors"
                >Explore guides</a>
            @endif
            <a
                href="{{ route('episodes.index') }}"
                class="dispatch-error-secondary rounded-control inline-flex min-h-12 items-center px-6 py-3 font-semibold transition-colors"
            >Listen to the podcast</a>
        </div>
    </x-error-state>
</x-layouts.error>
