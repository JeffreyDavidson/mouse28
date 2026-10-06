{{-- Static and token-free: it shows no subscriber data, so it is safe to reload or share. --}}
<x-layouts.app
    title="You’re confirmed | Mouse28"
    description="Your Mouse28 newsletter sign-up is confirmed."
    robots="noindex,nofollow"
    :canonical="route('home')"
    :dispatch-layout="true"
>
    <div class="dispatch-page-field bg-cream text-navy">
        <section class="mx-auto flex min-h-[60vh] max-w-6xl items-center px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
            <div class="max-w-3xl">
                <h1 class="font-heading max-w-[16ch] text-5xl/[1.04] [font-weight:660] tracking-[-0.03em] text-balance sm:text-6xl">
                    You’re confirmed
                </h1>
                <p class="text-navy/70 mt-6 max-w-[58ch] text-lg/8 text-pretty">
                    Thanks for confirming. New posts, episodes and park tips will land in your inbox, and every email
                    has an unsubscribe link at the bottom if it stops being useful.
                </p>

                <div class="mt-10 flex flex-wrap gap-3">
                    <a
                        href="{{ route('blog.index') }}"
                        class="bg-gold hover:bg-gold-light focus-visible:outline-purple inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
                    >
                        Read the blog
                    </a>
                    <a
                        href="{{ route('home') }}"
                        class="border-navy/20 hover:border-navy/40 focus-visible:outline-purple inline-flex min-h-12 items-center rounded-full border px-6 py-3 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
                    >
                        Back to home
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
