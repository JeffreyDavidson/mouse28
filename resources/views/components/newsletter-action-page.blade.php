@props([
    'title',
    'description',
    'actionUrl',
    'buttonLabel',
    'method' => 'POST',
])

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

                <form action="{{ $actionUrl }}" method="POST" class="mt-10">
                    @csrf
                    @if ($method !== 'POST')
                        @method($method)
                    @endif
                    <button
                        type="submit"
                        class="bg-gold hover:bg-gold-light focus-visible:outline-purple inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
                    >
                        {{ $buttonLabel }}
                    </button>
                </form>
            </div>
        </section>
    </div>
</x-layouts.app>
