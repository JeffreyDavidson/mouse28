<x-layouts.error
    title="Page Expired | Mouse28"
    description="Your Mouse28 session expired. Return to the form and try your request again."
    og-title="Page Expired | Mouse28"
    og-description="Your Mouse28 session expired. Return to the form and try your request again."
>
    <x-error-state
        code="419"
        title="Your session took a break"
        message="For your security, the form expired before it was submitted. Return to the contact page and try again."
    >
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('contact.show') }}"
                class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Return to contact</a>
            <a
                href="{{ route('home') }}"
                class="dispatch-error-secondary inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Go home</a>
        </div>
    </x-error-state>
</x-layouts.error>
