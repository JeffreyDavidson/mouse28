<?php

use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('episode editorial changes record the actor and changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    actingAs($editor);
    $record = Episode::factory()->create(['title' => 'Original title']);
    $created = Activity::query()->latest('id')->firstOrFail();

    $record->update(['title' => 'Updated title']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($created->event)->toBe('created')
        ->and($updated->event)->toBe('updated')
        ->and($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->subject_id)->toBe($record->id)
        ->and($updated->attribute_changes?->all() ?? [])->toEqual([
            'attributes' => ['title' => 'Updated title'],
            'old' => ['title' => 'Original title'],
        ]);

    $record->save();

    expect(Activity::query()->count())->toBe(2);

    $record->delete();
    $record->restore();

    expect(Activity::query()->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted', 'restored']);
});

test('episodes are ready to publish with a description and a Transistor episode URL', function (): void {
    $episode = Episode::factory()->draft()->make([
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
        'show_notes' => null,
        'featured_image_path' => null,
        'duration_seconds' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    expect($episode->publishingIssues())->toBeEmpty();
});

test('episodes cannot be published without each required detail', function (string $attribute, string $issue): void {
    $episode = Episode::factory()->draft()->make([
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
        $attribute => null,
    ]);

    expect($episode->publishingIssues())->toBe([$issue]);
})->with([
    'description' => ['description', 'Add a description'],
    'Transistor episode URL' => ['transistor_url', 'Add the Transistor episode URL'],
]);

test('an episode relates to every post linked to it', function (): void {
    $episode = Episode::factory()->create();
    $posts = Post::factory()->count(2)->create();

    $episode->posts()->attach($posts);

    expect($episode->posts->modelKeys())->toEqualCanonicalizing($posts->modelKeys());
});
