<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\ViewModels\SearchViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('search results select only the fields rendered by the page', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create([
        'title' => 'Sensory planning post',
        'body' => 'Sensory planning post body',
    ]);
    Guide::factory()->create([
        'title' => 'Sensory planning guide',
        'body' => 'Sensory planning guide body',
    ]);
    Episode::factory()->create([
        'title' => 'Sensory planning episode',
        'show_notes' => 'Sensory planning episode notes',
        'transcript' => 'Sensory planning episode transcript',
    ]);

    $results = app(SearchViewModel::class)->data('Sensory planning');

    expect($results['posts']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category'])
        ->not->toHaveKeys(['body', 'meta_description'])
        ->and($results['guides']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category'])
        ->not->toHaveKeys(['body', 'meta_description'])
        ->and($results['episodes']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'episode_number', 'title', 'description'])
        ->not->toHaveKeys(['show_notes', 'transcript', 'meta_description']);
});
