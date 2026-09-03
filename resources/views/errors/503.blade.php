<x-layouts.error
    title="We’ll Be Right Back | Mouse28"
    description="Mouse28 is temporarily unavailable while we make an update. Please try again shortly."
    og-title="We’ll Be Right Back | Mouse28"
    og-description="Mouse28 is temporarily unavailable while we make an update. Please try again shortly."
>
    <x-error-state
        code="503"
        title="We’re making a little magic"
        message="Mouse28 is temporarily unavailable while we make an update. Please try this page again shortly."
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
