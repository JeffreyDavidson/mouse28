<?php

use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('stats overview reports published content review needs drafts and active subscribers', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => [
            ['email' => 'active@example.com', 'unsubscribed' => false],
            ['email' => 'unsubscribed@example.com', 'unsubscribed' => true],
        ]]),
    ]);
    config()->set([
        'services.resend.audience_id' => 'test-audience',
        'services.resend.key' => 'test-key',
    ]);

    Post::factory()->create([
        'source_url' => 'https://example.com/source',
        'last_reviewed_at' => now()->subYear(),
    ]);
    Post::factory()->draft()->create();
    Guide::factory()->create(['last_reviewed_at' => now()]);
    Guide::factory()->draft()->create();
    Episode::factory()->create();
    Episode::factory()->draft()->create();

    $stats = livewire(StatsOverview::class)->instance()->getStats();

    expect(collect($stats)->map(fn (array $stat): array => [
        'label' => $stat['label'],
        'value' => $stat['value'],
        'description' => $stat['description'],
    ])->all())->toBe([
        [
            'label' => 'Guides',
            'value' => 1,
            'description' => 'Reviews current',
        ],
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
            'label' => 'Drafts',
            'value' => 3,
            'description' => 'All content',
        ],
        [
            'label' => 'Subscribers',
            'value' => 1,
            'description' => 'Active newsletter subscribers',
        ],
    ]);
});

test('stats overview reports unavailable subscribers when the provider fails', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response([], 503),
    ]);
    config()->set([
        'services.resend.audience_id' => 'test-audience',
        'services.resend.key' => 'test-key',
    ]);

    $stats = livewire(StatsOverview::class)->instance()->getStats();

    expect(collect($stats)->firstWhere('label', 'Subscribers'))
        ->toMatchArray([
            'value' => 0,
            'description' => 'Unavailable',
        ]);
});
