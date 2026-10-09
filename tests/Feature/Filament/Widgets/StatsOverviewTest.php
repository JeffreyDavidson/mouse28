<?php

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('stats overview reports published content review needs drafts and active subscribers', function (): void {
    Subscriber::factory()->create();
    Subscriber::factory()
        ->pending()
        ->create();
    Subscriber::factory()
        ->unsubscribed()
        ->create();

    Post::factory()->create([
        'source_url' => 'https://example.com/source',
        'last_reviewed_at' => now()->subYear(),
    ]);
    Post::factory()
        ->draft()
        ->create();
    Guide::factory()->create(['last_reviewed_at' => now()]);
    Guide::factory()
        ->draft()
        ->create();
    Episode::factory()->create();
    Episode::factory()
        ->draft()
        ->create();
    Episode::factory()->create(['status' => PublishStatus::InReview]);
    Post::factory()
        ->scheduled()
        ->create();

    $stats = livewire(StatsOverview::class)->instance()
        ->getStats();

    expect(collect($stats)->map(fn (array $stat): array => [
        'label' => $stat['label'],
        'value' => $stat['value'],
        'description' => $stat['description'],
    ])->all())->toBe([
        [
            'label' => 'Blog Posts',
            'value' => 1,
            'description' => '1 need review',
        ],
        [
            'label' => 'Episodes',
            'value' => 1,
            'description' => 'Published',
        ],
        [
            'label' => 'Guides',
            'value' => 1,
            'description' => 'Reviews current',
        ],
        [
            'label' => 'Drafts',
            'value' => 4,
            'description' => 'All content',
        ],
        [
            'label' => 'Subscribers',
            'value' => 1,
            'description' => 'Active newsletter subscribers',
        ],
    ]);
});

test('stats overview takes the content stats icon and colour from the content type', function (): void {
    $stats = collect(livewire(StatsOverview::class)->instance()
        ->getStats())->keyBy('label');

    foreach (ContentType::cases() as $type) {
        expect($stats[$type->pluralLabel()])
            ->toMatchArray([
                'icon' => $type->getIcon(),
                'textClass' => $type->textClass(),
            ]);
    }
});
