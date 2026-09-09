<?php

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

pest()->use(RefreshDatabase::class);

test('episode editorial changes record the actor and changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    \Pest\Laravel\actingAs($editor);
    $record = Episode::factory()->create(['title' => 'Original title']);
    $created = Activity::query()->latest('id')->firstOrFail();

    $record->update(['title' => 'Updated title']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($created->event)->toBe('created')
        ->and($updated->event)->toBe('updated')
        ->and($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->subject_id)->toBe($record->id)
        ->and($updated->attribute_changes?->all() ?? [])->toBe([
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
