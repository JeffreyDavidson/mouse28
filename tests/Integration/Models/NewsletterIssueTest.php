<?php

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

covers(NewsletterIssue::class);

pest()->use(RefreshDatabase::class);

test('a newsletter issue goes live through the shared publishing workflow', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');
    $issue = NewsletterIssue::factory()->draft()->create();

    expect(class_implements($issue))->toContain(Publishable::class)
        ->and($issue->isPublished())->toBeFalse();

    $issue->publish();

    expect($issue->refresh()->isPublished())->toBeTrue()
        ->and($issue->published_at?->toDateTimeString())->toBe('2026-09-25 12:00:00');

    $issue->unpublish();

    expect($issue->refresh()->isPublished())->toBeFalse();
});

test('newsletter issues are drafts, scheduled or live', function (): void {
    $live = NewsletterIssue::factory()->create();
    $scheduled = NewsletterIssue::factory()->scheduled()->create();
    $draft = NewsletterIssue::factory()->draft()->create();

    expect(NewsletterIssue::published()->pluck('id')->all())->toBe([$live->id])
        ->and(NewsletterIssue::scheduled()->pluck('id')->all())->toBe([$scheduled->id])
        ->and(NewsletterIssue::query()->where('status', PublishStatus::Draft)->pluck('id')->all())->toBe([$draft->id]);
});

test('an issue needs content before it can be published', function (string $content, array $issues): void {
    $issue = NewsletterIssue::factory()->draft()->make(['content' => $content]);

    expect($issue->publishingIssues())->toBe($issues);
})->with([
    'with content' => ['A short note.', []],
    'empty content' => ['', ['Add issue content']],
    'blank content' => ['   ', ['Add issue content']],
]);

test('an issue is sent once its sent date is set', function (): void {
    expect(NewsletterIssue::factory()->create()->wasSent())->toBeFalse()
        ->and(NewsletterIssue::factory()->sent()->create()->wasSent())->toBeTrue();
});

test('an issue keeps its deliveries and they follow it out of the database', function (): void {
    $issue = NewsletterIssue::factory()->create();
    $delivery = NewsletterDelivery::factory()->for($issue)->create();

    $issue->delete();

    expect($issue->deliveries()->pluck('id')->all())->toBe([$delivery->id]);

    $issue->forceDelete();

    expect(NewsletterDelivery::query()->count())->toBe(0);
});

test('issue edits record the editor and the changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    actingAs($editor);
    $issue = NewsletterIssue::factory()->create(['title' => 'Original title']);

    $issue->update(['title' => 'Updated title']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($updated->event)->toBe('updated')
        ->and($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->attribute_changes?->all() ?? [])->toEqual([
            'attributes' => ['title' => 'Updated title'],
            'old' => ['title' => 'Original title'],
        ]);

    $issue->save();

    expect(Activity::query()->count())->toBe(2);
});
