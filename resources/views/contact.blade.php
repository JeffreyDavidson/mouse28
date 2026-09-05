<x-layouts.app
    title="Contact | Mouse28"
    description="Contact Jeffrey and Cassie about Mouse28, Disney park accessibility, family travel, collaborations, or the podcast."
    og-title="Contact Mouse28"
    og-description="Get in touch with Jeffrey and Cassie about Disney park accessibility, family travel, collaborations, or the Mouse28 podcast."
    :canonical="route('contact.show')"
    :dispatch-layout="true"
>
    <!--
        THESIS: Contacting Mouse28 should feel like writing to Jeffrey and Cassie, not submitting a generic support ticket.
        OWN-WORLD: Cream correspondence paper, navy type, gold actions, quiet rules, and one purposeful white writing surface.
        STORY: Visitors understand who will read their note, see useful prompts, and send one clear message.
        FIRST VIEWPORT: A direct invitation leads into an asymmetric form and editorial note without repeated cards or actions.
        FORM [seed: direct-correspondence]: A focused editorial correspondence page inside the established Mouse28 world.
        FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
    -->
    @php
        $contactHasFeedback = $errors->contact->isNotEmpty();
        $firstContactError = $errors->contact->keys()[0] ?? null;
    @endphp

    <main data-contact-editorial class="dispatch-page-field bg-cream text-navy">
        @if (session('success'))
            <section class="mx-auto flex min-h-[70vh] max-w-6xl items-center px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
                <div class="max-w-3xl">
                    <h1 class="font-heading max-w-[12ch] text-5xl/[1.04] [font-weight:660] tracking-[-0.03em] text-balance sm:text-6xl lg:text-7xl">
                        Thanks for writing.
                    </h1>
                    <p class="text-navy/70 mt-6 max-w-[58ch] text-lg/8 text-pretty">
                        Your message is on its way to Jeffrey and Cassie. We usually reply within 48 hours.
                    </p>
                    <a
                        href="{{ route('blog.index') }}"
                        class="bg-gold hover:bg-gold-light focus-visible:outline-purple mt-10 inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
                    >Read the latest stories</a>
                </div>
            </section>
        @else
            <header class="border-navy/10 border-b">
                <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-18 lg:py-22">
                    <h1 class="font-heading max-w-[11ch] text-5xl/[1.02] [font-weight:660] tracking-[-0.03em] text-balance sm:text-6xl lg:text-7xl">
                        Send us a note.
                    </h1>
                    <p class="text-navy/70 mt-6 max-w-[62ch] text-lg/8 text-pretty">
                        Questions, park experiences, podcast ideas, and thoughtful partnerships are welcome here.
                    </p>
                    <p class="text-purple mt-3 font-semibold">
                        Jeffrey and Cassie read every message and usually reply within 48 hours.
                    </p>
                </div>
            </header>

            <section class="mx-auto grid max-w-6xl gap-12 px-4 py-14 sm:px-6 sm:py-18 lg:grid-cols-[4fr_8fr] lg:items-start lg:gap-20 lg:py-24">
                <aside class="lg:sticky lg:top-28">
                    <h2 class="font-heading max-w-[13ch] text-3xl/[1.15] [font-weight:620] tracking-[-0.02em] text-balance sm:text-4xl">
                        What would you like to share?
                    </h2>
                    <p class="text-navy/65 mt-5 max-w-[36ch] text-base/7 text-pretty">
                        A little context helps us understand your note and respond thoughtfully.
                    </p>

                    <ul
                        class="border-navy/15 divide-navy/15 mt-8 divide-y border-y"
                        aria-label="Suggested contact topics"
                    >
                        <li class="py-4">Park accessibility experiences</li>
                        <li class="py-4">Disney trip planning questions</li>
                        <li class="py-4">Podcast guest ideas</li>
                        <li class="py-4">Collaboration and partnerships</li>
                    </ul>

                    @if ($contactFormAvailable)
                        <p class="text-navy/65 mt-8 text-sm/6">
                            Prefer email?
                            <a
                                href="mailto:{{ $contactEmail }}"
                                class="text-purple decoration-gold/70 hover:text-navy font-semibold underline underline-offset-4 transition-colors"
                            >Write to {{ $contactEmail }}</a>
                        </p>
                    @endif
                </aside>

                @if ($contactFormAvailable)
                    <div class="dispatch-letter-form rounded-xl bg-white p-5 shadow-[0_1.75rem_4rem_rgb(26_16_64/0.12)] sm:p-8 lg:p-10">
                        <form action="{{ route('contact.store') }}" method="POST" class="flex flex-col gap-6">
                            @csrf
                            @if (config('services.turnstile.site_key'))
                                @once('turnstile-api')
                                    <script
                                        src="https://challenges.cloudflare.com/turnstile/v0/api.js"
                                        async
                                        defer
                                    ></script>
                                @endonce
                            @endif

                            @if ($errors->contact->has('contact_rate_limit'))
                                <div
                                    role="alert"
                                    class="rounded-xl border border-red-800/20 bg-red-50 px-4 py-3 text-sm text-red-900"
                                >
                                    {{ $errors->contact->first('contact_rate_limit') }}
                                </div>
                            @endif

                            <div class="absolute top-[-9999px] left-[-9999px]" aria-hidden="true">
                                <label for="website_url">Website</label>
                                <input
                                    type="text"
                                    id="website_url"
                                    name="website_url"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-2 block text-sm font-semibold">Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        value="{{ $contactHasFeedback ? old('name') : '' }}"
                                        placeholder="Your name"
                                        @error('name', 'contact') aria-invalid="true" aria-describedby="name-error" @enderror
                                        @if ($firstContactError === 'name') autofocus @endif
                                        class="border-navy/20 bg-dark-cream/45 text-navy placeholder:text-navy/65 focus:border-purple focus:ring-purple/15 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none"
                                    />
                                    @error('name', 'contact')
                                        <p id="name-error" role="alert" class="mt-2 text-sm text-red-800">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="mb-2 block text-sm font-semibold">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        required
                                        autocomplete="email"
                                        inputmode="email"
                                        value="{{ $contactHasFeedback ? old('email') : '' }}"
                                        placeholder="you@example.com"
                                        @error('email', 'contact') aria-invalid="true" aria-describedby="email-error" @enderror
                                        @if ($firstContactError === 'email') autofocus @endif
                                        class="border-navy/20 bg-dark-cream/45 text-navy placeholder:text-navy/65 focus:border-purple focus:ring-purple/15 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none"
                                    />
                                    @error('email', 'contact')
                                        <p id="email-error" role="alert" class="mt-2 text-sm text-red-800">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="mb-2 block text-sm font-semibold">Topic</label>
                                <select
                                    id="subject"
                                    name="subject"
                                    required
                                    @error('subject', 'contact') aria-invalid="true" aria-describedby="subject-error" @enderror
                                    @if ($firstContactError === 'subject') autofocus @endif
                                    class="border-navy/20 bg-dark-cream/45 text-navy focus:border-purple focus:ring-purple/15 min-h-12 w-full rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none"
                                >
                                    <option value="">Choose a topic...</option>
                                    <option
                                        value="general"
                                        @selected($contactHasFeedback && old('subject') === 'general')
                                    >
                                        General Question
                                    </option>
                                    <option
                                        value="accessibility"
                                        @selected($contactHasFeedback && old('subject') === 'accessibility')
                                    >
                                        Park Accessibility Question
                                    </option>
                                    <option
                                        value="collaboration"
                                        @selected($contactHasFeedback && old('subject') === 'collaboration')
                                    >
                                        Collaboration / Sponsorship
                                    </option>
                                    <option value="guest" @selected($contactHasFeedback && old('subject') === 'guest')>
                                        Guest on the Podcast
                                    </option>
                                    <option value="other" @selected($contactHasFeedback && old('subject') === 'other')>
                                        Other
                                    </option>
                                </select>
                                @error('subject', 'contact')
                                    <p id="subject-error" role="alert" class="mt-2 text-sm text-red-800">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="mb-2 block text-sm font-semibold">Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    required
                                    rows="7"
                                    placeholder="What's on your mind?"
                                    @error('message', 'contact') aria-invalid="true" aria-describedby="message-error" @enderror
                                    @if ($firstContactError === 'message') autofocus @endif
                                    class="border-navy/20 bg-dark-cream/45 text-navy placeholder:text-navy/65 focus:border-purple focus:ring-purple/15 min-h-44 w-full resize-y rounded-xl border px-4 py-3 text-base transition-colors focus:ring-2 focus:outline-none"
                                >{{ $contactHasFeedback ? old('message') : '' }}</textarea>
                                @error('message', 'contact')
                                    <p id="message-error" role="alert" class="mt-2 text-sm text-red-800">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                @if (config('services.turnstile.site_key'))
                                    <div
                                        class="cf-turnstile"
                                        data-sitekey="{{ config('services.turnstile.site_key') }}"
                                        data-action="{{ config('services.turnstile.contact_action') }}"
                                        data-theme="light"
                                        data-appearance="interaction-only"
                                    ></div>
                                @else
                                    <p role="alert" class="text-sm text-red-800">
                                        Contact verification is temporarily unavailable. Please try again later.
                                    </p>
                                @endif
                                @error('cf-turnstile-response', 'contact')
                                    <p id="turnstile-error" role="alert" class="mt-2 text-sm text-red-800">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="bg-gold hover:bg-gold-light focus-visible:outline-purple min-h-12 w-full rounded-xl px-6 py-3 text-base font-semibold transition-[background-color,transform] focus-visible:outline-2 focus-visible:outline-offset-4 active:translate-y-px sm:w-fit sm:min-w-44"
                            >
                                Send message
                            </button>
                        </form>
                    </div>
                @else
                    <div class="dispatch-letter-form rounded-xl bg-white p-6 shadow-[0_1.75rem_4rem_rgb(26_16_64/0.12)] sm:p-10 lg:p-12">
                        <h2 class="font-heading max-w-[15ch] text-4xl/[1.1] [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                            Email us directly.
                        </h2>
                        <p class="text-navy/70 mt-6 max-w-[54ch] text-base/7 text-pretty">
                            The secure form is unavailable right now, but your message can still reach Jeffrey and
                            Cassie.
                        </p>
                        <a
                            href="mailto:{{ $contactEmail }}"
                            class="bg-gold hover:bg-gold-light focus-visible:outline-purple mt-8 inline-flex min-h-12 max-w-full items-center rounded-xl px-5 py-3 font-semibold break-all transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 sm:px-6"
                        >{{ $contactEmail }}</a>
                    </div>
                @endif
            </section>
        @endif
    </main>
</x-layouts.app>
