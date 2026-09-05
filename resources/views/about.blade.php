<x-layouts.app
    title="About the Davidson Family | Mouse28"
    description="Meet Jeffrey and Cassie Davidson and learn why their family shares Disney park accessibility experiences through Mouse28."
    og-description="Meet Jeffrey and Cassie Davidson and learn why their family shares Disney park accessibility experiences through Mouse28."
    og-image="/images/hero-family.jpg"
    :canonical="route('about')"
    :dispatch-layout="true"
>
    <!--
        THESIS: Mouse28 is a family field journal shaped by repeat park days and Viola's way of seeing the world.
        OWN-WORLD: Candid park photography, navy cloth, cream paper, gold rules, and generous editorial type.
        STORY: Meet the family in motion, understand what they learned, discover why they share it, then meet each host.
        FIRST VIEWPORT: A real park moment leads; the headline reads like the cover line of a family profile.
        FORM [seed: park-photo-essay]: A photographic magazine feature ending in two distinct personal profiles.
    -->
    <div data-about-editorial>
        <header class="bg-navy text-cream px-4 pt-5 pb-10 sm:px-6 sm:pt-8 sm:pb-14 lg:pt-10 lg:pb-20">
            <div class="mx-auto max-w-[86rem]">
                <div class="grid gap-6 pb-8 sm:pb-10 lg:grid-cols-[8fr_4fr] lg:items-end lg:gap-16">
                    <h1 class="font-heading text-cream max-w-[18ch] text-[2.75rem]/[1.02] [font-weight:680] tracking-[-0.03em] text-balance sm:text-6xl lg:text-7xl">
                        Disney looks different through our family's eyes.
                    </h1>
                    <p class="text-cream/75 max-w-xl text-lg/8 text-pretty lg:pb-1">
                        We're Jeffrey and Cassie, the parents, park regulars, and voices behind Mouse28.
                    </p>
                </div>

                <figure>
                    <div class="overflow-hidden rounded-xl">
                        <picture>
                            <source
                                srcset="/images/hero-family-640.webp 640w, /images/hero-family-1024.webp 1024w, /images/hero-family.webp 2048w"
                                sizes="(min-width: 1400px) 1376px, calc(100vw - 2rem)"
                                type="image/webp"
                            />
                            <img
                                src="/images/hero-family.jpg"
                                alt="Jeffrey and Cassie enjoying the Kilimanjaro Safaris at Disney's Animal Kingdom"
                                width="2048"
                                height="1536"
                                fetchpriority="high"
                                class="aspect-[5/4] w-full object-cover object-center sm:aspect-[16/9] lg:aspect-[16/7]"
                            />
                        </picture>
                    </div>
                    <figcaption class="text-cream/60 mt-3 text-right text-sm">
                        Kilimanjaro Safaris at Disney's Animal Kingdom
                    </figcaption>
                </figure>
            </div>
        </header>

        <main class="dispatch-page-field bg-cream">
            <section class="mx-auto grid max-w-6xl gap-8 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[5fr_7fr] lg:gap-20 lg:py-28">
                <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                    We stopped measuring a Disney day by how much we did.
                </h2>
                <div class="text-navy/75 max-w-[70ch] space-y-5 text-[1.0625rem]/8 text-pretty">
                    <p>
                        We're a Central Florida family who returns to Disney often. The parks became our place for
                        Saturday afternoons, birthday celebrations, and the days when we simply needed to be somewhere
                        familiar together.
                    </p>
                    <p>
                        Going back changed the way we experienced them. We had time to notice which routines helped,
                        where the quiet corners were, and when changing the plan was better than pushing through it.
                        Success stopped meaning a perfect itinerary. It became a day that worked for our family.
                    </p>
                </div>
            </section>

            <section class="mx-auto max-w-[86rem] px-4 pb-16 sm:px-6 sm:pb-20 lg:pb-28">
                <div class="grid overflow-hidden rounded-xl bg-white lg:grid-cols-[5fr_7fr]">
                    <img
                        src="/images/meet-jeffrey-and-cassie.webp"
                        alt="Jeffrey and Cassie Davidson at Disney's Hollywood Studios"
                        width="800"
                        height="1200"
                        loading="lazy"
                        decoding="async"
                        class="h-full max-h-[48rem] w-full object-cover object-[center_35%]"
                    />
                    <div class="flex flex-col justify-center px-6 py-12 sm:px-10 sm:py-16 lg:px-16 lg:py-20">
                        <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                            Familiarity gave us room to follow Viola's lead.
                        </h2>
                        <div class="text-navy/75 mt-7 max-w-[66ch] space-y-5 text-[1.0625rem]/8 text-pretty">
                            <p>
                                Viola is autistic. Like many parents, we spent time learning what helps her thrive, what
                                overwhelms her, and how to make unfamiliar environments more manageable. Disney became
                                one of the places that works for her. Not perfectly, but meaningfully.
                            </p>
                            <p>
                                We have navigated meltdowns in Tomorrowland, sensory overload in gift shops, and days
                                when a familiar routine mattered more than one more ride. Accessibility programs such as
                                Disability Access Service help, but so do quiet spaces, flexible plans, and permission
                                to change course.
                            </p>
                            <p>
                                Viola notices what we would otherwise miss: water moving through a fountain, the care a
                                character gives a nervous child, and the comfort of returning to a favorite place.
                                Following her lead changed what we believe a good park day can be.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-navy text-cream">
                <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
                    <p class="font-heading max-w-[19ch] text-4xl/[1.12] [font-weight:580] tracking-[-0.025em] text-balance italic sm:text-5xl/[1.12] lg:text-6xl/[1.12]">
                        The real magic was never doing everything. It was finding a way to experience it together.
                    </p>
                    <div class="bg-gold mt-10 h-px w-28"></div>
                </div>
            </section>

            <section class="mx-auto grid max-w-6xl gap-8 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[5fr_7fr] lg:gap-20 lg:py-28">
                <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                    Mouse28 is what we wish we had found sooner.
                </h2>
                <div class="text-navy/75 max-w-[70ch] space-y-5 text-[1.0625rem]/8 text-pretty">
                    <p>
                        Other families kept asking how we approach Disney: how we prepare, which places feel less
                        overwhelming, and what we do when a plan stops working. Years of lived experience had given us
                        practical answers that could help someone else feel more prepared and less alone.
                    </p>
                    <p>
                        The name Mouse28 nods to 1928, the year Mickey Mouse made his debut in
                        <em>Steamboat Willie</em>. This is where we share honest family stories, accessibility
                        conversations, and the useful park details we learned by being there.
                    </p>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 pt-2">
                        <a
                            href="{{ route('blog.index') }}"
                            class="text-purple decoration-gold/70 hover:text-navy inline-flex min-h-12 items-center font-semibold underline underline-offset-8 transition-colors"
                        >Read our stories</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            class="text-purple decoration-gold/70 hover:text-navy inline-flex min-h-12 items-center font-semibold underline underline-offset-8 transition-colors"
                        >Hear the podcast</a>
                    </div>
                </div>
            </section>

            <section class="bg-dark-cream border-navy/10 border-t" aria-labelledby="voices-heading">
                <div class="mx-auto max-w-[86rem] px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
                    <div class="max-w-4xl">
                        <h2
                            id="voices-heading"
                            class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl lg:text-6xl"
                        >
                            Two people. Two perspectives. One family.
                        </h2>
                        <p class="text-navy/70 mt-5 max-w-[62ch] text-lg/8 text-pretty">
                            Jeffrey and Cassie share the same story, but each brings something different to Mouse28.
                        </p>
                    </div>

                    <div class="border-gold/40 mt-12 grid border-y md:grid-cols-2 lg:mt-16">
                        <article class="border-gold/40 py-10 md:border-r md:pr-10 lg:py-14 lg:pr-16">
                            <img
                                src="/images/jeffrey.webp"
                                alt="Jeffrey Davidson in Epcot's Japan Pavilion"
                                width="2048"
                                height="2048"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[5/4] w-full rounded-xl object-cover object-center shadow-[0_1.5rem_3.5rem_rgb(26_16_64/0.14)]"
                            />
                            <h3 class="font-heading text-navy mt-8 text-4xl [font-weight:620] tracking-[-0.02em]">
                                Jeffrey Davidson
                            </h3>
                            <p class="text-purple mt-2 font-semibold">Dad, software engineer, and Disney strategist</p>
                            <div class="text-navy/70 mt-6 max-w-[62ch] space-y-4 text-base/7 text-pretty">
                                <p>
                                    Jeffrey is the planner and podcast editor, the person mapping a park route,
                                    researching the details, and asking whether there is time for one more ride.
                                </p>
                                <p>
                                    After more than 15 years building things on the internet, he brings the same care
                                    for useful information to everything Mouse28 publishes.
                                </p>
                            </div>
                            <dl class="border-navy/15 mt-7 grid gap-5 border-t pt-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-navy/55 text-sm">Favorite park</dt>
                                    <dd class="text-navy mt-1 font-semibold">Epcot</dd>
                                </div>
                                <div>
                                    <dt class="text-navy/55 text-sm">Behind the scenes</dt>
                                    <dd class="text-navy mt-1 font-semibold">Planning and production</dd>
                                </div>
                            </dl>
                        </article>

                        <article class="border-gold/40 border-t py-10 md:border-t-0 md:pl-10 lg:py-14 lg:pl-16">
                            <img
                                src="/images/cassie.webp"
                                alt="Cassie Davidson at Magic Kingdom's Winnie the Pooh"
                                width="2048"
                                height="2048"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[5/4] w-full rounded-xl object-cover object-center shadow-[0_1.5rem_3.5rem_rgb(26_16_64/0.14)]"
                            />
                            <h3 class="font-heading text-navy mt-8 text-4xl [font-weight:620] tracking-[-0.02em]">
                                Cassie Davidson
                            </h3>
                            <p class="text-purple mt-2 font-semibold">Mom, baker, and accessibility champion</p>
                            <div class="text-navy/70 mt-6 max-w-[62ch] space-y-4 text-base/7 text-pretty">
                                <p>
                                    Cassie keeps every story grounded in what genuinely helps families. She brings
                                    warmth, honesty, and the practical instinct to know when the plan needs to change.
                                </p>
                                <p>
                                    Away from the parks, she runs a cottage food bakery, wrangles two huskies, and keeps
                                    Mouse28's advice connected to real family life.
                                </p>
                            </div>
                            <dl class="border-navy/15 mt-7 grid gap-5 border-t pt-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-navy/55 text-sm">Favorite park</dt>
                                    <dd class="text-navy mt-1 font-semibold">Magic Kingdom</dd>
                                </div>
                                <div>
                                    <dt class="text-navy/55 text-sm">Behind the scenes</dt>
                                    <dd class="text-navy mt-1 font-semibold">Family perspective and care</dd>
                                </div>
                            </dl>
                        </article>
                    </div>
                </div>
            </section>

            <section class="bg-navy text-cream border-gold/40 border-t">
                <div class="mx-auto flex max-w-6xl flex-col gap-7 px-4 py-14 sm:px-6 sm:py-16 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="font-heading text-3xl [font-weight:620] tracking-[-0.015em] text-balance sm:text-4xl">
                            Come spend a park day with us.
                        </h2>
                        <p class="text-cream/70 mt-3 max-w-xl text-base/7">
                            Start with a story or join our latest conversation.
                        </p>
                    </div>
                    <a
                        href="{{ route('blog.index') }}"
                        class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 w-fit items-center rounded-full px-6 py-3 font-semibold transition-colors"
                    >Explore Mouse28</a>
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
