@props([
    'code',
    'title',
    'message',
])

<section class="dispatch-error-stage relative isolate flex min-h-[calc(100dvh-10rem)] items-center overflow-hidden py-12 sm:py-16">
    <div class="relative mx-auto w-full max-w-5xl px-4 sm:px-6">
        <div class="dispatch-error-sheet grid items-center gap-10 overflow-hidden rounded-2xl px-6 py-10 sm:px-10 sm:py-12 md:grid-cols-[minmax(0,1fr)_15rem] md:px-14 md:py-16">
            <div class="min-w-0 wrap-anywhere">
                <h1 class="font-heading text-navy max-w-[15ch] text-4xl/tight font-bold tracking-[-0.025em] sm:text-5xl md:text-6xl">
                    {{ $title }}
                </h1>
                <p class="text-navy/70 mt-5 max-w-2xl text-base/relaxed sm:text-lg/relaxed">{{ $message }}</p>

                <div class="dispatch-error-recovery mt-8">{{ $slot }}</div>
            </div>

            <div class="dispatch-error-marker" aria-hidden="true">
                <span class="font-heading">{{ $code }}</span>
            </div>
        </div>
    </div>
</section>
