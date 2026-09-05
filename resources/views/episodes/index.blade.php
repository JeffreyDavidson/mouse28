<x-layouts.app
    :title="($podcast->name ?: 'Mouse28').' Podcast'"
    :description="$podcast->description ?: 'Disney park stories, accessibility conversations, and family experiences from the Mouse28 podcast.'"
    :og-image="$podcast->cover_image ? '/storage/'.ltrim($podcast->cover_image, '/') : '/images/podcast/mouse28-cover.jpg'"
    :canonical="$canonicalUrl"
    :dispatch-layout="true"
    :show-footer-newsletter="$episodes->isEmpty()"
>
    <!--
        THESIS: The podcast page behaves like a show and listening archive, not a dashboard of episode widgets.
        OWN-WORLD: Navy cloth, real cover artwork, cream track sheets, gold controls, purple links, and quiet rules.
        STORY: Visitors meet the show, choose the newest episode or listening service, then browse each season.
        FIRST VIEWPORT: Podcast artwork anchors the left while the show promise and newest episode lead on the right.
        FORM [seed: season-tracklist]: Show poster followed by a spacious season tracklist with no sidebar or statistics panels.
    -->
    @php
        $allEpisodes = $episodes->getCollection();
        $latestEpisode = $allEpisodes->first();
        $groupedEpisodes = $allEpisodes->groupBy(fn ($episode) => $episode->season_number ?? 0);
        $coverImage = $podcast->cover_image
            ? '/storage/'.ltrim($podcast->cover_image, '/')
            : '/images/podcast/mouse28-cover.jpg';
    @endphp

    <div data-podcast-archive>
        <section class="podcast-show-hero bg-navy text-cream relative overflow-hidden">
            <div class="relative mx-auto grid max-w-[86rem] gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[5fr_7fr] lg:items-center lg:gap-16 lg:py-20">
                <div class="podcast-cover-frame mx-auto w-full max-w-lg lg:mx-0">
                    <img
                        src="{{ $coverImage }}"
                        alt="Mouse28 podcast artwork"
                        width="1200"
                        height="1200"
                        fetchpriority="high"
                        class="aspect-square w-full rounded-xl object-cover"
                    />
                </div>

                <div class="max-w-3xl">
                    <h1 class="font-heading text-5xl [font-weight:680] tracking-[-0.025em] text-balance sm:text-6xl">
                        The Mouse28 Podcast
                    </h1>
                    <p class="text-cream/75 mt-5 max-w-2xl text-base/7 text-pretty sm:text-lg/8">
                        Honest Disney conversations about accessibility, family life, favorite places, and what we learn
                        in the parks.
                    </p>

                    @if ($latestEpisode)
                        <div class="border-gold/35 mt-8 border-y py-6">
                            <h2 class="font-heading text-2xl [font-weight:620] tracking-[-0.015em] text-balance sm:text-3xl">
                                {{ $latestEpisode->title }}
                            </h2>
                            <div class="text-cream/65 mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                <span>{{ $latestEpisode->published_at->format('F j, Y') }}</span>
                                @if ($latestEpisode->duration_seconds)
                                    <span>{{ $latestEpisode->formatted_duration }}</span>
                                @endif
                                @if ($latestEpisode->season_number)
                                    <span>Season {{ $latestEpisode->season_number }}, episode {{ $latestEpisode->episode_number }}</span>
                                @endif
                            </div>
                            <a
                                href="{{ route('episodes.show', $latestEpisode) }}"
                                class="bg-gold text-navy hover:bg-gold-light mt-5 inline-flex min-h-12 items-center rounded-full px-6 py-3 font-semibold transition-colors"
                            >{{ $latestEpisode->audio_source_url ? 'Listen now' : 'Episode details' }}</a>
                        </div>
                    @else
                        <div class="border-gold/35 mt-8 border-y py-6">
                            <h2 class="font-heading text-2xl [font-weight:620] tracking-[-0.015em]">
                                We're warming up the mics
                            </h2>
                            <p class="text-cream/70 mt-3 max-w-xl text-base/7">
                                Our first episode is in the works. Subscribe so you're there from the start.
                            </p>
                        </div>
                    @endif

                    @if ($podcast->distributionLinks())
                        <nav aria-label="Listen to the Mouse28 podcast" class="mt-6 flex flex-wrap gap-3">
                            @foreach ($podcast->distributionLinks() as $link)
                                <a
                                    href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="border-cream/20 text-cream hover:border-gold hover:text-gold inline-flex min-h-12 items-center rounded-full border px-5 py-2.5 text-sm font-semibold transition-colors"
                                >{{ $link['label'] }}</a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            </div>
        </section>

        <section class="dispatch-page-field bg-cream py-12 sm:py-16 lg:py-20">
            <div class="mx-auto max-w-[86rem] px-4 sm:px-6">
                @if ($episodes->count())
                    <div class="mb-10 max-w-2xl">
                        <h2 class="font-heading text-navy text-4xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-5xl">
                            Episode archive
                        </h2>
                        <p class="text-navy/70 mt-4 text-base/7 text-pretty">
                            Start with the newest conversation or work through a season in order.
                        </p>
                    </div>

                    <div class="podcast-ledger">
                        @foreach ($groupedEpisodes as $season => $seasonEpisodes)
                            <section class="{{ ! $loop->first ? 'mt-14' : '' }}" aria-labelledby="season-{{ $season }}">
                                <div class="border-gold/45 flex flex-wrap items-baseline justify-between gap-3 border-b pb-4">
                                    <h3
                                        id="season-{{ $season }}"
                                        class="font-heading text-navy text-3xl [font-weight:620] tracking-[-0.015em]"
                                    >
                                        {{ $season > 0 ? 'Season '.$season : 'Episodes' }}
                                    </h3>
                                    <p class="text-navy/60 text-sm">
                                        {{ $seasonEpisodes->count() }} {{ Str::plural('episode', $seasonEpisodes->count()) }}
                                    </p>
                                </div>

                                <div class="podcast-tracklist">
                                    @foreach ($seasonEpisodes as $episode)
                                        <article class="podcast-track group">
                                            <a
                                                href="{{ route('episodes.show', $episode) }}"
                                                class="grid min-h-40 gap-5 py-7 sm:grid-cols-[5rem_minmax(0,1fr)_auto] sm:items-center sm:gap-7"
                                            >
                                                <div class="text-purple font-heading text-4xl [font-weight:680] tabular-nums sm:text-center">
                                                    {{ str_pad((string) $episode->episode_number, 2, '0', STR_PAD_LEFT) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-navy/60 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                                        <span>{{ $episode->published_at->format('M j, Y') }}</span>
                                                        @if ($episode->duration_seconds)
                                                            <span>{{ $episode->formatted_duration }}</span>
                                                        @endif
                                                    </div>
                                                    <h4 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl [font-weight:600] tracking-[-0.015em] text-balance transition-colors">
                                                        {{ $episode->title }}
                                                    </h4>
                                                    <p class="text-navy/70 mt-3 max-w-3xl text-base/7 text-pretty">
                                                        {{ Str::limit($episode->description, 185) }}
                                                    </p>
                                                </div>
                                                <span class="text-purple decoration-gold/70 inline-flex min-h-12 shrink-0 items-center font-semibold underline underline-offset-8">
                                                    {{ $episode->audio_source_url ? 'Listen now' : 'Episode details' }}
                                                </span>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    @if ($episodes->hasPages())
                        <div class="episodes-pagination mt-14 flex justify-center">{{ $episodes->links() }}</div>
                    @endif

                    <div class="mx-auto mt-16 max-w-3xl">
                        <x-newsletter-card subtitle="New episodes and Disney tips delivered to your inbox" />
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.app>
