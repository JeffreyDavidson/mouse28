@php($recovery = \App\Support\ErrorRecovery::for(request(), 429))

<x-layouts.error
    title="Too Many Requests | Mouse28"
    description="You’re moving a little faster than Mouse28 can keep up. Please wait a minute and try again."
    og-title="Too Many Requests | Mouse28"
>
    <x-error-state
        code="429"
        title="Let’s take a breather"
        message="You’re moving a little faster than we can keep up. Please wait a minute, then try again."
    >
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $recovery['url'] }}"
                class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >{{ $recovery['label'] }}</a>
            <a
                href="{{ route('home') }}"
                class="dispatch-error-secondary inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
            >Go home</a>
        </div>
    </x-error-state>
</x-layouts.error>
