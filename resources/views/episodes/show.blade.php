<x-layouts.app
    :title="($episode->meta_title ?: $episode->title).' — Mouse28'"
    :description="$episode->meta_description ?: Str::limit($episode->description, 160)"
    :og-title="$episode->meta_title ?: $episode->title"
    :og-description="$episode->meta_description ?: Str::limit($episode->description, 200)"
    :og-image="$episode->og_image_url ?: $episode->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
    :dispatch-layout="true"
    :show-footer-newsletter="false"
>
    <!--
        THESIS: An episode page should behave like a listening sheet, not a dashboard of podcast widgets.
        OWN-WORLD: Navy show cloth, real cover artwork, cream track sheets, Besley headlines, and quiet gold rules.
        STORY: Meet the episode, listen, choose a platform, read the notes or transcript, then continue the season.
        FIRST VIEWPORT: Cover artwork and the episode's listening action share one focused show poster.
        FORM [seed: listening-sheet]: Show poster followed by a single linear notes and transcript column.
    -->
    @unless ($isPreview ?? false)
        @push('head')
            <x-structured-data :data="\App\Support\StructuredData::forEpisode($episode, $podcast)" />
        @endpush
    @endunless

    @if ($isPreview ?? false)
        <div role="status" class="bg-gold text-navy px-4 py-3 text-center text-sm font-semibold">
            Preview mode — this page is only visible to administrators.
        </div>
    @endif

    @php
        $showNotesLength = Str::of(strip_tags($episode->show_notes ?? ''))->squish()->length();
        $isSparseEpisode = blank($episode->audio_source_url)
            && blank($episode->transcript)
            && $showNotesLength < 160;
        $coverImage = $episode->cover_image_url
            ?: ($podcast->cover_image ? '/storage/'.ltrim($podcast->cover_image, '/') : '/images/podcast/mouse28-cover.jpg');
        $appleUrl = $episode->apple_url ?: $podcast->apple_url;
        $spotifyUrl = $episode->spotify_url ?: $podcast->spotify_url;
        $youtubeUrl = $episode->youtube_url ?: $podcast->youtube_url;
    @endphp

    <section class="episode-detail-hero bg-navy text-cream relative overflow-hidden">
        <div class="relative mx-auto grid max-w-[86rem] gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[5fr_7fr] lg:items-center lg:gap-16 lg:py-20">
            <div class="podcast-cover-frame mx-auto w-full max-w-md lg:mx-0">
                <img
                    src="{{ $coverImage }}"
                    alt="{{ $episode->title }} podcast artwork"
                    width="1200"
                    height="1200"
                    fetchpriority="high"
                    class="aspect-square w-full rounded-xl object-cover"
                />
            </div>

            <div class="min-w-0 wrap-anywhere">
                <a
                    href="{{ route('episodes.index') }}"
                    class="text-cream/65 hover:text-gold inline-flex min-h-12 items-center gap-2 text-sm font-semibold transition-colors"
                >
                    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    All Episodes
                </a>

                <div class="text-cream/65 mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm" data-episode-meta>
                    <span class="text-gold font-semibold">Episode {{ $episode->episode_number }}</span>
                    @if ($episode->season_number)
                        <span>Season {{ $episode->season_number }}</span>
                    @endif
                    @if ($episode->duration_seconds)
                        <span>{{ $episode->formatted_duration }}</span>
                    @endif
                    <span>{{ $episode->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
                </div>

                <h1 class="font-heading mt-4 max-w-4xl text-4xl/tight [font-weight:680] tracking-[-0.03em] text-balance sm:text-5xl/tight lg:text-6xl/tight">
                    {{ $episode->title }}
                </h1>

                @if ($episode->description)
                    <p class="text-cream/72 mt-6 max-w-3xl text-lg/8 text-pretty">{{ $episode->description }}</p>
                @endif

                @if ($episode->audio_source_url)
                    <div class="border-gold/30 mt-8 border-y py-6">
                        <h2 class="font-heading text-xl [font-weight:620]">Listen to this episode</h2>
                        <audio controls aria-label="Play {{ $episode->title }}" class="mt-4 w-full" preload="metadata">
                            <source src="{{ $episode->audio_source_url }}" type="audio/mpeg" />
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                @endif

                <nav aria-label="Podcast listening options" class="mt-6 flex flex-wrap gap-x-5 gap-y-2">
                    @if ($appleUrl)
                        <a
                            href="{{ $appleUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gold hover:text-cream inline-flex min-h-12 flex-col justify-center"
                        >
                            <span class="font-semibold underline underline-offset-8">Apple Podcasts</span>
                            <span class="text-cream/55 text-xs">{{ $episode->apple_url ? 'Listen to this episode' : 'Visit the show' }}</span>
                        </a>
                    @endif
                    @if ($spotifyUrl)
                        <a
                            href="{{ $spotifyUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gold hover:text-cream inline-flex min-h-12 flex-col justify-center"
                        >
                            <span class="font-semibold underline underline-offset-8">Spotify</span>
                            <span class="text-cream/55 text-xs">{{ $episode->spotify_url ? 'Listen to this episode' : 'Visit the show' }}</span>
                        </a>
                    @endif
                    @if ($youtubeUrl)
                        <a
                            href="{{ $youtubeUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gold hover:text-cream inline-flex min-h-12 flex-col justify-center"
                        >
                            <span class="font-semibold underline underline-offset-8">YouTube</span>
                            <span class="text-cream/55 text-xs">{{ $episode->youtube_url ? 'Watch this episode' : 'Visit the channel' }}</span>
                        </a>
                    @endif
                    <a
                        href="{{ route('rss.podcast') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-gold hover:text-cream inline-flex min-h-12 flex-col justify-center"
                    >
                        <span class="font-semibold underline underline-offset-8">RSS Feed</span>
                        <span class="text-cream/55 text-xs">Subscribe in another podcast app</span>
                    </a>
                </nav>
            </div>
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-12 sm:py-16 lg:py-20">
        <div data-episode-layout="{{ $isSparseEpisode ? 'sparse' : 'rich' }}" class="mx-auto max-w-4xl px-4 sm:px-6">
            <dl class="border-gold/35 grid gap-x-8 border-y py-6 sm:grid-cols-3">
                <div class="flex min-h-12 items-center justify-between gap-4 sm:block">
                    <dt class="text-navy/60 text-sm">Episode</dt>
                    <dd class="text-navy font-semibold">{{ $episode->episode_number }}</dd>
                </div>
                @if ($episode->duration_seconds)
                    <div class="flex min-h-12 items-center justify-between gap-4 sm:block">
                        <dt class="text-navy/60 text-sm">Duration</dt>
                        <dd class="text-navy font-semibold">{{ $episode->formatted_duration }}</dd>
                    </div>
                @endif
                <div class="flex min-h-12 items-center justify-between gap-4 sm:block">
                    <dt class="text-navy/60 text-sm">Published</dt>
                    <dd class="text-navy font-semibold">
                        {{ $episode->published_at?->format('M j, Y') ?? 'Not scheduled' }}
                    </dd>
                </div>
            </dl>

            @if ($episode->show_notes)
                <section class="mt-12" aria-labelledby="show-notes-heading">
                    <h2
                        id="show-notes-heading"
                        class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em]"
                    >
                        Show notes
                    </h2>
                    <div class="episode-show-notes-content mt-6 wrap-anywhere">{!! $episode->show_notes !!}</div>
                </section>
            @endif

            <section class="border-navy/12 mt-12 border-t pt-12" aria-labelledby="transcript-heading">
                <h2
                    id="transcript-heading"
                    class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em]"
                >
                    Transcript
                </h2>
                @if ($episode->transcript)
                    <div class="mt-6" x-data="{ expanded: false }">
                        <div
                            id="episode-transcript"
                            tabindex="0"
                            class="episode-transcript-content max-h-[600px] wrap-anywhere"
                            :class="{ 'max-h-none': expanded }"
                        >
                            {!! $episode->transcript !!}
                        </div>
                        <div class="relative" x-show="! expanded" x-cloak>
                            <div class="from-cream pointer-events-none absolute inset-x-0 bottom-full h-20 bg-linear-to-t to-transparent"></div>
                        </div>
                        <button
                            type="button"
                            @click="expanded = ! expanded"
                            :aria-expanded="expanded.toString()"
                            aria-controls="episode-transcript"
                            class="border-navy/15 text-purple hover:border-purple mt-5 min-h-12 w-full rounded-xl border px-5 py-3 text-center font-semibold transition-colors"
                        >
                            <span x-text="expanded ? 'Collapse Transcript' : 'Read Full Transcript'">Read Full Transcript</span>
                        </button>
                    </div>
                @else
                    <p class="text-navy/65 mt-5 italic">A transcript is not available for this episode.</p>
                @endif
            </section>

            <section class="border-navy/12 mt-12 border-t pt-8" aria-labelledby="share-episode" data-print-hidden>
                <h2 id="share-episode" class="font-heading text-navy text-xl [font-weight:620]">Share this episode</h2>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                    <a
                        href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — Mouse28 Podcast') }}&url={{ urlencode(route('episodes.show', $episode)) }}"
                        target="_blank"
                        rel="noopener"
                        class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
                    >Post on X</a>
                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('episodes.show', $episode)) }}"
                        target="_blank"
                        rel="noopener"
                        class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
                    >Share on Facebook</a>
                    <button
                        type="button"
                        data-copy-link
                        class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
                    >
                        <span data-copy-label>Copy Link</span>
                        <span data-copy-feedback class="hidden" role="status" aria-live="polite" aria-atomic="true"
                            >Copied!</span>
                    </button>
                </div>
            </section>

            @if ($relatedPosts->count())
                <section
                    class="border-navy/12 mt-12 border-t pt-10"
                    aria-labelledby="episode-related-posts"
                    data-print-hidden
                >
                    <h2 id="episode-related-posts" class="font-heading text-navy text-2xl [font-weight:620]">
                        Stories from this episode
                    </h2>
                    <div class="mt-6 grid gap-7 sm:grid-cols-2">
                        @foreach ($relatedPosts as $post)
                            <article class="group min-w-0">
                                <a
                                    href="{{ route('blog.show', $post) }}"
                                    aria-label="Read {{ $post->title }}"
                                    class="block overflow-hidden rounded-xl"
                                >
                                    <x-post-artwork
                                        :post="$post"
                                        :compact="true"
                                        class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
                                    />
                                </a>
                                <h3 class="font-heading text-navy group-hover:text-purple mt-4 text-xl [font-weight:600] transition-colors">
                                    <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                                </h3>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-episode-continuation
                :previous-episode="$previousEpisode"
                :next-episode="$nextEpisode"
                :compact="$isSparseEpisode"
            />

            <div class="mx-auto mt-14 max-w-3xl" data-print-hidden>
                <x-newsletter-card subtitle="New episodes and Disney tips delivered to your inbox" />
            </div>
        </div>
    </section>
</x-layouts.app>
