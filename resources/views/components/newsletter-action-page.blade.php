@props([
    'title',
    'description',
    'actionUrl',
    'buttonLabel',
    'method' => 'POST',
    'pendingLabel' => null,
])

{{-- A pending label makes the form submit itself once the page starts (the newsletterConfirm component in resources/js/app.js). --}}
{{-- The token is in the path, so the canonical points home instead of echoing the private link. --}}
<x-layouts.app
    :title="$title.' | Mouse28'"
    :description="$description"
    robots="noindex,nofollow"
    :canonical="route('home')"
    :dispatch-layout="true"
>
    <div class="dispatch-page-field bg-cream text-navy">
        <section class="mx-auto flex min-h-[60vh] max-w-6xl items-center px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
            <div class="max-w-3xl">
                <h1 class="font-heading max-w-[16ch] text-5xl/[1.04] [font-weight:660] tracking-[-0.03em] text-balance sm:text-6xl">
                    {{ $title }}
                </h1>
                <p class="text-navy/70 mt-6 max-w-[58ch] text-lg/8 text-pretty">{{ $description }}</p>

                <form
                    action="{{ $actionUrl }}"
                    method="POST"
                    class="mt-10"
                    @if ($pendingLabel)
                        data-newsletter-confirm
                        x-data="newsletterConfirm"
                    @endif
                >
                    @csrf
                    @if ($method !== 'POST')
                        @method($method)
                    @endif
                    @if ($pendingLabel)
                        <p role="status" class="text-navy/70 mb-4 text-base" hidden x-bind:hidden="notConfirming">
                            {{ $pendingLabel }}
                        </p>
                    @endif
                    <button
                        type="submit"
                        @if ($pendingLabel)
                            x-bind:disabled="confirming"
                        @endif
                        class="bg-gold hover:bg-gold-light focus-visible:outline-purple inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 disabled:cursor-wait disabled:opacity-70"
                    >
                        {{ $buttonLabel }}
                    </button>
                </form>
            </div>
        </section>
    </div>
</x-layouts.app>
