<x-layouts.error
    title="Something Went Wrong — Mouse28"
    description="Mouse28 could not complete this request. Please try again in a moment."
    og-title="Something Went Wrong — Mouse28"
    og-description="Mouse28 could not complete this request. Please try again in a moment."
>
    <x-error-state
        code="500"
        eyebrow="Unexpected error"
        title="The magic hit a snag"
        message="Something unexpected happened while loading this page. Please try again in a moment."
    >
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a
                href="{{ url()->current() }}"
                class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Try again</a>
            <a
                href="{{ route('home') }}"
                class="hover:border-gold/50 hover:text-gold inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors"
            >Go home</a>
        </div>
    </x-error-state>
</x-layouts.error>
