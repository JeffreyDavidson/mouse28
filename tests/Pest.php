<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use App\Support\PrimaryPodcast;
use Dom\HTMLDocument;
use Dom\XPath;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\PendingCommand;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\BrowserTestCase;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

require_once __DIR__.'/Browser/helpers.php';

/** @param array<string, mixed> $parameters */
function pendingCommand(string $command, array $parameters = []): PendingCommand
{
    $pendingCommand = artisan($command, $parameters);

    if (! $pendingCommand instanceof PendingCommand) {
        throw new LogicException('The Artisan command did not return a pending command.');
    }

    return $pendingCommand;
}

/** The site's saved podcast show, created with the configured defaults when missing. */
function primaryPodcast(): Podcast
{
    return app(PrimaryPodcast::class)->findOrCreate();
}

/**
 * The ORDER BY clause of every query the callback runs that orders by publish time.
 *
 * @return list<string>
 */
function publishTimeOrderings(Closure $callback): array
{
    $orderings = [];
    DB::listen(function (QueryExecuted $query) use (&$orderings): void {
        if (preg_match('/order by (.*?)(?: limit| offset|$)/i', $query->sql, $match) === 1 && str_contains($match[1], 'published_at')) {
            $orderings[] = $match[1];
        }
    });

    $callback();

    return $orderings;
}

/**
 * A link to the named route whose signature is missing or no longer matches the address.
 *
 * @param  array<string, mixed>  $parameters
 */
function invalidlySignedRoute(string $name, array $parameters, string $signature): string
{
    return match ($signature) {
        'unsigned' => route($name, $parameters),
        'tampered' => URL::signedRoute($name, $parameters).'0',
        default => throw new InvalidArgumentException("Unknown signature case {$signature}."),
    };
}

dataset('invalid signatures', ['unsigned', 'tampered']);

dataset('existing and missing records', [
    'existing record' => [true],
    'missing record' => [false],
]);

/** Publish-time order with an id tie-breaker in the same direction, so equal times keep a stable order on MySQL. */
const STABLE_PUBLISH_TIME_ORDER = '/"published_at" (asc|desc), "(?:\w+"\.")?id" \1/';

pest()->extend(TestCase::class)
    ->in('Feature', 'Integration');

pest()->extend(BrowserTestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Browser');

/**
 * @param  array<string, mixed>  $archive
 * @return array<array-key, mixed>
 */
function firstArchivedRecord(array $archive, string $type): array
{
    $records = $archive[$type] ?? null;

    if (! is_array($records) || ! is_array($records[0] ?? null)) {
        throw new UnexpectedValueException("The archive has no {$type}.");
    }

    return $records[0];
}

/**
 * Returns the archive with its first record of a type changed, optionally dropping keys.
 *
 * @param  array<string, mixed>  $archive
 * @param  array<string, mixed>  $changes
 * @param  list<string>  $without
 * @return array<string, mixed>
 */
function withFirstArchivedRecord(array $archive, string $type, array $changes, array $without = []): array
{
    $record = firstArchivedRecord($archive, $type);
    foreach ($without as $key) {
        unset($record[$key]);
    }

    return [...$archive, $type => [[...$record, ...$changes]]];
}

dataset('archived written content', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

/** Creates an administrator and signs in as them. */
function actingAsAdmin(): User
{
    $admin = User::factory()
        ->admin()
        ->create();

    actingAs($admin);

    return $admin;
}

/** Gives Turnstile test keys and the production actions and hostnames, so the contact and newsletter forms render their widgets. */
function configureTurnstile(): void
{
    config()->set('services.turnstile', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
        'siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'contact_action' => 'contact-form',
        'newsletter_action' => 'newsletter',
        'allowed_hostnames' => ['mouse28.com', 'www.mouse28.com'],
    ]);
}

/**
 * The response body parsed as an HTML document.
 *
 * @param  TestResponse<Response>  $response
 */
function responseDocument(TestResponse $response): HTMLDocument
{
    $content = $response->getContent();

    if ($content === false) {
        throw new UnexpectedValueException('The response body could not be read.');
    }

    return HTMLDocument::createFromString($content, LIBXML_NOERROR);
}

/**
 * How many <main> landmarks the response renders.
 *
 * @param  TestResponse<Response>  $response
 */
function mainLandmarkCount(TestResponse $response): int
{
    return new XPath(responseDocument($response))
        ->query('//*[local-name()="main"]')
        ->count();
}

/**
 * Fills a Filament create page and asserts that creating fails with the given errors.
 *
 * @param  class-string<CreateRecord>  $createPage
 * @param  array<string, mixed>  $state
 * @param  array<array-key, mixed>  $errors
 */
function assertCreateFormErrors(string $createPage, array $state, array $errors): void
{
    livewire($createPage)
        ->fillForm($state)
        ->call('create')
        ->assertHasFormErrors($errors);
}

/**
 * Asserts the resource listing shows the readiness column and a persisted In Review status.
 *
 * @param  class-string<Post|Guide|Episode>  $model
 */
function assertListShowsReadinessAndStatus(string $url, string $model): void
{
    $model::factory()->create(['status' => PublishStatus::InReview]);

    get($url)
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('In Review');
}

/**
 * Asserts the drafts tab lists drafts and records in review, and the scheduled tab lists scheduled records.
 *
 * @param  class-string<ListRecords>  $listPage
 * @param  class-string<Post|Guide|Episode>  $model
 */
function assertListTabsFilterDraftsAndScheduled(string $listPage, string $model): void
{
    $draft = $model::factory()
        ->draft()
        ->create();
    $inReview = $model::factory()->create(['status' => PublishStatus::InReview]);
    $scheduled = $model::factory()
        ->scheduled()
        ->create();

    livewire($listPage)
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft, $inReview])
        ->assertCanNotSeeTableRecords([$scheduled])
        ->set('activeTab', 'scheduled')
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft]);
}

/**
 * Asserts the listing header counts only live records as published and both drafts and records in review as drafts.
 *
 * @param  class-string<ListRecords>  $listPage
 * @param  class-string<Post|Guide|Episode>  $model
 */
function assertListHeaderCountsOnlyPublished(string $listPage, string $model): void
{
    $model::factory()->create();
    $model::factory()
        ->scheduled()
        ->create();
    $model::factory()
        ->draft()
        ->create();
    $model::factory()->create(['status' => PublishStatus::InReview]);

    $page = livewire($listPage);
    $component = $page->instance();

    expect($component->getHeader()
        ?->getData())
        ->toMatchArray([
            'published' => 1,
            'drafts' => 2,
        ]);
}

/**
 * Saves the edit form with the given state and asserts the record keeps its slug.
 *
 * @param  class-string<EditRecord>  $editPage
 * @param  array<string, mixed>  $state
 */
function assertEditKeepsSlug(string $editPage, Post|Guide|Episode $record, array $state): void
{
    $slug = $record->slug;

    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm($state)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->slug)->toBe($slug);
}

/**
 * Asserts a draft's slug rejects characters that cannot form a public route.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditRejectsInvalidDraftSlug(string $editPage, Post|Guide|Episode $draft): void
{
    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
}

/**
 * Freezes time and asserts the edit page links to the draft's 24-hour signed preview in a new tab.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditOffersDraftPreview(string $editPage, Post|Guide|Episode $draft, string $previewRoute): void
{
    Date::setTestNow(Date::now());

    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute($previewRoute, Date::now()->addHours(24), [lcfirst(class_basename($draft)) => $draft]))
        ->assertActionShouldOpenUrlInNewTab('preview');
}

/**
 * Publishes the draft from its edit page and asserts it went live with a publication date.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditPublishes(string $editPage, Post|Guide|Episode $draft, string $notification): void
{
    livewire($editPage, ['record' => $draft->getRouteKey()])
        ->callAction('publish')
        ->assertNotified($notification);

    expect($draft->refresh()
        ->status)->toBe(PublishStatus::Published)
        ->and($draft->published_at)
        ->not->toBeNull();
}

/**
 * Unpublishes the record from its edit page and asserts it is a draft again.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditUnpublishes(string $editPage, Post|Guide|Episode $published, string $notification): void
{
    livewire($editPage, ['record' => $published->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified($notification);

    expect($published->refresh()
        ->status)->toBe(PublishStatus::Draft);
}

/**
 * Deletes the record, asserts its public page is gone, then restores it from the edit page and asserts the page is back.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditRestoresDeleted(string $editPage, Post|Guide|Episode $published, string $publicUrl): void
{
    $published->delete();

    get($publicUrl)->assertNotFound();

    livewire($editPage, ['record' => $published->getRouteKey()])
        ->callAction('restore')
        ->assertNotified();

    assertNotSoftDeleted($published);
    get($publicUrl)->assertOk();
}

/**
 * Saves an SEO title and description from the edit page and asserts they reach the SEO row.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditSavesSeo(string $editPage, Post|Guide|Episode $record): void
{
    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm([
            'seo.title' => 'A saved SEO title',
            'seo.description' => 'A saved SEO description.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->seo?->title)->toBe('A saved SEO title')
        ->and($record->seo?->description)
        ->toBe('A saved SEO description.');
}

/**
 * Gives the record a stored cover, replaces it from the edit page, and asserts the new cover and its
 * variants are stored under the directory while the previous cover and its variants are removed.
 *
 * @param  class-string<EditRecord>  $editPage
 */
function assertEditReplacesCover(string $editPage, Post|Guide|Episode $record, string $directory): void
{
    Storage::disk('public')->put("{$directory}/previous.png", UploadedFile::fake()
        ->image('previous.png', 1000, 525)
        ->getContent());
    $record->update(['featured_image_path' => "{$directory}/previous.png"]);

    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm(['featured_image_path' => [UploadedFile::fake()->image('cover.png', 1000, 525)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = (string) $record->refresh()
        ->featured_image_path;
    expect($path)->toStartWith("{$directory}/")
        ->not->toBe("{$directory}/previous.png");
    Storage::disk('public')->assertExists([$path, "{$directory}/responsive/".pathinfo($path, PATHINFO_FILENAME).'-480.webp']);
    Storage::disk('public')->assertMissing(["{$directory}/previous.png", "{$directory}/responsive/previous-480.webp"]);
}
