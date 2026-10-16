@if ($review)
    @error('review')
        <div role="alert" class="bg-danger-50 text-danger-700 rounded-lg p-4">{{ $message }}</div>
    @enderror
    <section aria-label="Saved review set" class="space-y-6">
        <div class="rounded-xl bg-white p-6 ring-1 ring-gray-950/5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">{{ $review->title }} · {{ $review->version }}</h2>
                <x-filament::badge :color="$review->status->getColor()">{{ $review->status->getLabel() }}</x-filament::badge>
            </div>
            <p class="mt-3 text-sm text-gray-600">
                Open each saved image to read it at full size before recording your decision.
            </p>
            @if ($review->sourcePost)
                <p class="mt-3">
                    <a
                        class="text-primary-600 underline"
                        href="{{ \JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $review->sourcePost]) }}"
                    >Source article: {{ $review->sourcePost->title }}</a>
                </p>
            @endif
            @if ($review->reviewed_at)
                <p class="mt-3 text-sm text-gray-600">
                    Reviewed by {{ $review->reviewer?->name ?? 'Deleted administrator' }} · {{ $review->reviewed_at->timezone(config('app.display_timezone'))->format('M j, Y g:i A T') }}
                </p>
            @endif
            @if ($review->review_notes)
                <p class="mt-3 whitespace-pre-wrap">{{ $review->review_notes }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach ($review->files as $index => $file)
                <figure class="rounded-xl bg-white p-4 ring-1 ring-gray-950/5">
                    <a
                        class="focus-visible:outline-primary-600 block rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4"
                        href="{{ route('filament.admin.assetReviews.file', ['assetReview' => $review, 'file' => $index]) }}"
                        target="_blank"
                        rel="noopener"
                    >
                        <img
                            class="h-auto w-full rounded-lg"
                            src="{{ route('filament.admin.assetReviews.file', ['assetReview' => $review, 'file' => $index]) }}"
                            alt="Open {{ $review->title }}, image {{ $index + 1 }}, at full size (opens in a new tab)"
                            loading="lazy"
                        />
                    </a>
                    <figcaption class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                        <span>Image {{ $index + 1 }} of {{ count($review->files) }}</span>
                        <a
                            class="text-primary-600 underline"
                            href="{{ route('filament.admin.assetReviews.file', ['assetReview' => $review, 'file' => $index, 'download' => true]) }}"
                        >Download image</a>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
