<?php

use App\Enums\ContentType;
use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

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

test('stats overview colours its gold metrics with the readable gold ink', function (): void {
    // Act
    $textClasses = collect(livewire(StatsOverview::class)->instance()
        ->getStats())
        ->pluck('textClass', 'label')
        ->only(['Episodes', 'Drafts'])
        ->all();

    // Assert
    expect($textClasses)->toBe([
        'Episodes' => 'text-mouse-gold-ink',
        'Drafts' => 'text-mouse-gold-ink',
    ]);
});

test('stats overview steps from two to three to five columns as its panel widens', function (): void {
    // Act
    $component = livewire(StatsOverview::class);

    // Assert
    $component->assertSeeHtml('grid-cols-2')
        ->assertSeeHtml('@xl:grid-cols-3')
        ->assertSeeHtml('@4xl:grid-cols-5');
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
