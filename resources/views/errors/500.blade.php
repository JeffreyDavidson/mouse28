<x-layouts.error
    title="Something Went Wrong | Mouse28"
    description="Mouse28 could not complete this request. Please try again in a moment."
    og-title="Something Went Wrong | Mouse28"
    og-description="Mouse28 could not complete this request. Please try again in a moment."
>
    <x-error-state
        code="500"
        title="The magic hit a snag"
        message="Something unexpected happened while loading this page. Please try again in a moment."
    >
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ url()->current() }}"
                class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Try again</a>
            <a
                href="{{ route('home') }}"
                class="dispatch-error-secondary inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Go home</a>
        </div>
    </x-error-state>
</x-layouts.error>
