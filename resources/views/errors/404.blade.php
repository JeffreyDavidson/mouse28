<x-layouts.error
    title="Page Not Found — Mouse28"
    description="The page you requested could not be found. Search Mouse28 or continue exploring our Disney park stories, guides, and podcast."
    og-title="Page Not Found — Mouse28"
    og-description="Search Mouse28 or continue exploring our Disney park stories, accessibility guides, and podcast."
>
    <x-error-state
        code="404"
        eyebrow="Page not found"
        title="That page wandered off"
        message="The address may have changed, or the page may no longer be available. Search Mouse28 or choose a place to keep exploring."
    >
        <form
            action="{{ route('search') }}"
            method="GET"
            role="search"
            class="mx-auto mt-8 flex max-w-xl flex-col gap-3 sm:flex-row"
        >
            <label for="not-found-search" class="sr-only">Search Mouse28</label>
            <input
                id="not-found-search"
                type="search"
                name="q"
                maxlength="100"
                placeholder="Search posts, guides, and episodes"
                class="text-navy placeholder:text-navy/35 focus:border-gold focus:ring-gold/40 min-h-12 min-w-0 flex-1 rounded-full border border-white/15 bg-white px-5 py-3 text-base shadow-lg focus:ring-2 focus:outline-none"
            />
            <button
                type="submit"
                class="bg-gold text-navy hover:bg-gold-light min-h-12 rounded-full px-7 py-3 font-semibold shadow-lg transition-colors"
            >
                Search
            </button>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a
                href="{{ route('home') }}"
                class="text-navy hover:bg-cream inline-flex min-h-12 items-center rounded-full bg-white px-6 py-3 font-semibold transition-colors"
            >Go home</a>
            <a
                href="{{ route('blog.index') }}"
                class="hover:border-gold/50 hover:text-gold inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors"
            >Browse blog</a>
            <a
                href="{{ route('guides.index') }}"
                class="hover:border-gold/50 hover:text-gold inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors"
            >Explore guides</a>
            <a
                href="{{ route('episodes.index') }}"
                class="hover:border-gold/50 hover:text-gold inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors"
            >Listen to podcast</a>
        </div>
    </x-error-state>
</x-layouts.error>
