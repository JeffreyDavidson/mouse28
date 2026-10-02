<?php

use App\Models\Concerns\SyncsLegacyBody;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(SyncsLegacyBody::class);

pest()->use(RefreshDatabase::class);

dataset('legacy body content', [
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
]);

test('saving mirrors the content into the legacy body', function (PostFactory|GuideFactory $factory, ?string $content, string $body): void {
    $record = $factory->createOne(['content' => $content]);

    expect(DB::table($record->getTable())->where('id', $record->getKey())->value('body'))->toBe($body);
})->with('legacy body content')->with([
    'markdown' => ["## Arrival\n\nPlan a flexible arrival.", "## Arrival\n\nPlan a flexible arrival."],
    'empty' => ['', ''],
    'missing' => [null, ''],
]);

test('editing the content keeps the legacy body in step', function (PostFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['content' => 'First draft.']);

    $record->update(['content' => 'Second draft.']);

    expect(DB::table($record->getTable())->where('id', $record->getKey())->value('body'))->toBe('Second draft.');
})->with('legacy body content');

test('a legacy body written directly is overwritten by the content on save', function (PostFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['content' => 'Current content.']);

    $record->forceFill(['body' => 'Stale body.'])->save();

    expect(DB::table($record->getTable())->where('id', $record->getKey())->value('body'))->toBe('Current content.');
})->with('legacy body content');

test('saving a record loaded without its content leaves the legacy body alone', function (PostFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['content' => 'Loaded elsewhere.']);
    $partial = $record->newQuery()->select(['id', 'title'])->whereKey($record->getKey())->firstOrFail();

    $partial->update(['title' => 'Renamed']);

    expect(DB::table($record->getTable())->where('id', $record->getKey())->value('body'))->toBe('Loaded elsewhere.');
})->with('legacy body content');
