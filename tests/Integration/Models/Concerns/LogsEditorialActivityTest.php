<?php

use App\Models\Category;
use App\Models\Concerns\LogsEditorialActivity;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;

covers(LogsEditorialActivity::class);

dataset('editorial models', [
    'post' => [new Post, [
        'title',
        'slug',
        'excerpt',
        'content',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'source_url',
        'last_reviewed_at',
        'featured_image_path',
        'category_id',
        'status',
        'published_at',
    ]],
    'guide' => [new Guide, [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'featured_image_path',
        'source_url',
        'last_reviewed_at',
        'status',
        'published_at',
    ]],
    'episode' => [new Episode, [
        'title',
        'slug',
        'description',
        'show_notes',
        'transcript',
        'episode_number',
        'season_number',
        'transistor_url',
        'youtube_url',
        'podcast_id',
        'guest_name',
        'guest_title',
        'guest_url',
        'duration_seconds',
        'featured_image_path',
        'status',
        'published_at',
    ]],
    'newsletter issue' => [new NewsletterIssue, [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'sent_at',
    ]],
    'category' => [new Category, ['name', 'slug', 'description']],
    'podcast' => [new Podcast, [
        'name',
        'slug',
        'description',
        'long_description',
        'cover_image_path',
        'color',
        'apple_url',
        'spotify_url',
        'youtube_url',
        'is_active',
        'sort_order',
    ]],
]);

test('editorial models log their editable attributes in the editorial log', function (Post|Guide|Episode|NewsletterIssue|Category|Podcast $model, array $attributes): void {
    // Act
    $options = $model->getActivitylogOptions();

    // Assert
    expect($options->logName)->toBe('editorial')
        ->and($options->logAttributes)
        ->toBe($attributes);
})->with('editorial models');

test('editorial models log only changed values and skip saves that change nothing', function (Post|Guide|Episode|NewsletterIssue|Category|Podcast $model): void {
    // Act
    $options = $model->getActivitylogOptions();

    // Assert
    expect($options->logOnlyDirty)->toBeTrue()
        ->and($options->logEmptyChanges)
        ->toBeFalse();
})->with('editorial models');
