<?php

use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(HasTagsUntilForceDeleted::class);

pest()->use(RefreshDatabase::class);

dataset('taggable content', [
    'post' => [fn (): Post => Post::factory()->create()],
    'episode' => [fn (): Episode => Episode::factory()->create()],
    'guide' => [fn (): Guide => Guide::factory()->create()],
]);

test('trashing and restoring content keeps its tags', function (Post|Episode|Guide $content): void {
    $content->attachTags(['Accessibility', 'Magic Kingdom'], 'content');

    $content->delete();
    $content->restore();

    expect($content->refresh()
        ->tagsWithType('content')
        ->pluck('name')
        ->sort()
        ->values()
        ->all())
        ->toBe(['Accessibility', 'Magic Kingdom']);
})->with('taggable content');

test('trashed content keeps its tags while it is in the trash', function (Post|Episode|Guide $content): void {
    $content->attachTag('Accessibility', 'content');

    $content->delete();

    expect(DB::table('taggables')
        ->where('taggable_type', $content->getMorphClass())
        ->where('taggable_id', $content->getKey())
        ->count())->toBe(1);
})->with('taggable content');

test('force deleting content detaches its tags', function (Post|Episode|Guide $content): void {
    $content->attachTag('Accessibility', 'content');

    $content->forceDelete();

    expect(DB::table('taggables')
        ->where('taggable_type', $content->getMorphClass())
        ->where('taggable_id', $content->getKey())
        ->count())->toBe(0);
})->with('taggable content');

test('editorial content models keep tags until they are force deleted', function (string $model): void {
    expect(class_uses_recursive($model))->toContain(HasTagsUntilForceDeleted::class);
})->with([
    'post' => [Post::class],
    'episode' => [Episode::class],
    'guide' => [Guide::class],
]);
