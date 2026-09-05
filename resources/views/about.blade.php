<x-layouts.app
    title="About the Davidson Family — Mouse28"
    description="Meet Jeffrey and Cassie Davidson and learn why their family shares Disney park accessibility experiences through Mouse28."
    og-description="Meet Jeffrey and Cassie Davidson and learn why their family shares Disney park accessibility experiences through Mouse28."
    og-image="/images/meet-jeffrey-and-cassie.jpg"
    :canonical="route('about')"
    :dispatch-layout="true"
>
    <!--
        THESIS: Mouse28 begins with a real family and the parks they have learned to navigate together.
        OWN-WORLD: Navy cloth, cream paper, Besley headlines, gold rules, and candid park photography.
        STORY: Meet Jeffrey and Cassie, understand what Disney means to their family, then discover why they share it.
        FIRST VIEWPORT: Their portrait carries equal weight with a short, direct introduction.
        FORM [seed: family-profile]: A personal editorial profile with two distinct host portraits and one continuous story.
    -->
    <div data-about-editorial>
        <section class="editorial-detail-hero bg-navy text-cream relative overflow-hidden">
            <div class="relative mx-auto grid max-w-[86rem] lg:min-h-[42rem] lg:grid-cols-[5fr_7fr]">
                <div class="dispatch-page-heading flex min-w-0 flex-col justify-center px-4 py-14 wrap-anywhere sm:px-6 sm:py-20 lg:py-24 lg:pr-14">
                    <h1 class="font-heading text-5xl [font-weight:680] tracking-[-0.025em] text-balance sm:text-6xl">
                        Our Story
                    </h1>
                    <p class="text-cream/75 mt-5 max-w-[33rem] text-lg/8 text-pretty">
                        Disney gave our family a place to learn, connect, and experience the world at Viola's pace.
                    </p>
                    <a
                        href="#our-story"
                        class="text-gold decoration-gold/70 hover:text-cream mt-7 inline-flex min-h-12 w-fit items-center font-semibold underline underline-offset-8 transition-colors"
                    >Read our story</a>
                </div>

                <figure class="relative min-h-[28rem] overflow-hidden lg:min-h-full">
                    <img
                        src="/images/meet-jeffrey-and-cassie.webp"
                        alt="Jeffrey and Cassie Davidson at Disney's Hollywood Studios"
                        width="800"
                        height="1200"
                        fetchpriority="high"
                        class="absolute inset-0 size-full object-cover object-[center_34%]"
                    />
                    <div class="from-navy/80 absolute inset-x-0 bottom-0 bg-linear-to-t to-transparent px-5 pt-24 pb-5 lg:px-7">
                        <figcaption class="text-cream/80 text-sm">
                            Jeffrey and Cassie at Disney's Hollywood Studios
                        </figcaption>
                    </div>
                </figure>
            </div>
        </section>

        <main id="our-story" class="dispatch-page-field bg-cream">
            <section class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[minmax(0,7fr)_minmax(16rem,3fr)] lg:gap-20 lg:py-28">
                <div class="max-w-[72ch]">
                    <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-5xl">
                        Disney became part of our family rhythm.
                    </h2>
                    <div class="text-navy/75 mt-7 space-y-5 text-[1.0625rem]/8 text-pretty">
                        <p>
                            We're Jeffrey and Cassie, and we live in Central Florida with our daughter Viola. Disney
                            World became our place for Saturday afternoons, birthday celebrations, and the days when our
                            family simply needed to get out of the house together.
                        </p>
                        <p>
                            Living close to the parks changed them from a once-in-a-lifetime destination into somewhere
                            familiar. That familiarity gave us room to slow down, notice what helped Viola feel
                            comfortable, and build the kind of park days that work for our family.
                        </p>
                    </div>
                </div>

                <blockquote class="border-gold/45 text-navy self-start border-y py-7">
                    <p class="font-heading text-2xl/9 [font-weight:560] tracking-[-0.015em] text-pretty italic">
                        “Viola taught us that slowing down and paying attention is where the real magic lives.”
                    </p>
                </blockquote>
            </section>

            <section class="border-navy/12 mx-auto max-w-6xl border-t px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
                <div class="max-w-[72ch] lg:ml-auto lg:w-[72%]">
                    <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-5xl">
                        Seeing the parks through Viola's eyes
                    </h2>
                    <div class="text-navy/75 mt-7 space-y-5 text-[1.0625rem]/8 text-pretty">
                        <p>
                            Viola is autistic. Like many parents, we spent time learning what helps her thrive, what
                            overwhelms her, and how to make unfamiliar environments feel manageable. Disney turned out
                            to be one of the places that works for her—not perfectly, but meaningfully.
                        </p>
                        <p>
                            We have navigated meltdowns in Tomorrowland, sensory overload in gift shops, and days when a
                            familiar routine mattered more than fitting in one more ride. Accessibility programs such as
                            Disability Access Service have helped, but so have quiet corners, flexible plans, and a
                            willingness to change course.
                        </p>
                        <p>
                            Viola notices details we would otherwise miss: water moving through a fountain, the care a
                            character gives a nervous child, or the comfort of returning to a favorite place. Following
                            her lead changed how we experience the parks and what we believe a successful Disney day can
                            look like.
                        </p>
                    </div>
                </div>
            </section>

            <section class="bg-navy text-cream">
                <div class="mx-auto grid max-w-6xl gap-8 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[4fr_7fr] lg:gap-20 lg:py-24">
                    <h2 class="font-heading text-4xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-5xl">
                        Why Mouse28 exists
                    </h2>
                    <div class="text-cream/75 max-w-[70ch] space-y-5 text-[1.0625rem]/8 text-pretty">
                        <p>
                            Other families kept asking how we approach Disney: how we prepare, which places feel less
                            overwhelming, and what we do when a plan stops working. We realized that years of lived
                            experience could help someone else feel more prepared and less alone.
                        </p>
                        <p>
                            The name Mouse28 nods to 1928, the year Mickey Mouse made his debut in
                            <em>Steamboat Willie</em>. This is where we share the practical lessons, honest stories, and
                            accessibility conversations our family wishes we had found sooner.
                        </p>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:py-28" aria-labelledby="voices-heading">
                <div class="max-w-3xl">
                    <h2
                        id="voices-heading"
                        class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-5xl"
                    >
                        The two voices behind Mouse28
                    </h2>
                    <p class="text-navy/70 mt-5 max-w-[62ch] text-lg/8 text-pretty">
                        We share the same family story, but each of us brings a different perspective to the work.
                    </p>
                </div>

                <article class="border-navy/12 mt-12 grid gap-8 border-t pt-10 md:grid-cols-[5fr_7fr] md:items-center md:gap-14 lg:mt-16 lg:gap-20">
                    <img
                        src="/images/jeffrey.webp"
                        alt="Jeffrey Davidson in Epcot's Japan Pavilion"
                        width="2048"
                        height="2048"
                        loading="lazy"
                        decoding="async"
                        class="aspect-[4/5] w-full rounded-xl object-cover object-center shadow-[0_1.5rem_3.5rem_rgb(26_16_64/0.14)]"
                    />
                    <div class="min-w-0">
                        <h3 class="font-heading text-navy text-3xl [font-weight:620] tracking-[-0.015em] sm:text-4xl">
                            Jeffrey Davidson
                        </h3>
                        <p class="text-purple mt-2 font-semibold">Dad, software engineer, and Disney strategist</p>
                        <div class="text-navy/70 mt-6 space-y-4 text-base/7 text-pretty">
                            <p>
                                Jeffrey is the planner and podcast editor—the person mapping a park route, researching
                                the details, and asking whether there is time for one more ride before heading home.
                            </p>
                            <p>
                                He has spent more than 15 years building things on the internet. Mouse28 brings that
                                instinct for useful information together with what our family has learned in the parks.
                            </p>
                        </div>
                        <dl class="border-gold/35 mt-7 grid gap-5 border-y py-5 sm:grid-cols-2">
                            <div>
                                <dt class="text-navy/55 text-sm">Favorite park</dt>
                                <dd class="text-navy mt-1 font-semibold">Epcot</dd>
                            </div>
                            <div>
                                <dt class="text-navy/55 text-sm">Behind the scenes</dt>
                                <dd class="text-navy mt-1 font-semibold">Planning and production</dd>
                            </div>
                        </dl>
                    </div>
                </article>

                <article class="border-navy/12 mt-14 grid gap-8 border-t pt-10 md:grid-cols-[7fr_5fr] md:items-center md:gap-14 lg:mt-20 lg:gap-20">
                    <div class="order-2 min-w-0 md:order-1">
                        <h3 class="font-heading text-navy text-3xl [font-weight:620] tracking-[-0.015em] sm:text-4xl">
                            Cassie Davidson
                        </h3>
                        <p class="text-purple mt-2 font-semibold">Mom, baker, and accessibility champion</p>
                        <div class="text-navy/70 mt-6 space-y-4 text-base/7 text-pretty">
                            <p>
                                Cassie keeps every story grounded in what genuinely helps families. She brings warmth,
                                honesty, and the practical perspective of the person who knows when the plan needs to
                                change.
                            </p>
                            <p>
                                Away from the parks, she runs a cottage food bakery, wrangles two huskies, and keeps the
                                family moving. She is the heart of Mouse28 and the reason its advice stays human.
                            </p>
                        </div>
                        <dl class="border-gold/35 mt-7 grid gap-5 border-y py-5 sm:grid-cols-2">
                            <div>
                                <dt class="text-navy/55 text-sm">Favorite park</dt>
                                <dd class="text-navy mt-1 font-semibold">Magic Kingdom</dd>
                            </div>
                            <div>
                                <dt class="text-navy/55 text-sm">Behind the scenes</dt>
                                <dd class="text-navy mt-1 font-semibold">Family perspective and care</dd>
                            </div>
                        </dl>
                    </div>
                    <img
                        src="/images/cassie.webp"
                        alt="Cassie Davidson at Magic Kingdom's Winnie the Pooh"
                        width="2048"
                        height="2048"
                        loading="lazy"
                        decoding="async"
                        class="order-1 aspect-[4/5] w-full rounded-xl object-cover object-center shadow-[0_1.5rem_3.5rem_rgb(26_16_64/0.14)] md:order-2"
                    />
                </article>
            </section>

            <section class="border-gold/35 bg-navy text-cream border-t">
                <div class="mx-auto flex max-w-6xl flex-col gap-7 px-4 py-14 sm:px-6 sm:py-16 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="font-heading text-3xl [font-weight:620] tracking-[-0.015em] text-balance sm:text-4xl">
                            Come explore with us.
                        </h2>
                        <p class="text-cream/70 mt-3 max-w-xl text-base/7">
                            Start with a family story or join our latest conversation.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <a
                            href="{{ route('blog.index') }}"
                            class="bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
                        >Read the blog</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            class="border-cream/25 text-cream hover:border-gold hover:text-gold inline-flex min-h-12 items-center rounded-full border px-6 py-3 font-semibold transition-colors"
                        >Listen to the podcast</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
