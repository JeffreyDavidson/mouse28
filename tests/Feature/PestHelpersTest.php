<?php

use App\Enums\GuideCategory;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('actingAsAdmin signs in a new administrator', function (): void {
    // Act
    $admin = actingAsAdmin();

    // Assert
    assertAuthenticatedAs($admin);
    expect($admin->is_admin)->toBeTrue();
});

test('configureTurnstile sets test keys so the contact form renders its widget', function (): void {
    // Act
    configureTurnstile();

    // Assert
    expect(config('services.turnstile'))->toMatchArray([
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
        'siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'contact_action' => 'contact-form',
        'newsletter_action' => 'newsletter',
        'allowed_hostnames' => ['mouse28.com', 'www.mouse28.com'],
    ]);
    get(route('contact.create'))
        ->assertOk()
        ->assertSeeHtml('data-sitekey="test-site-key"');
});

test('mainLandmarkCount counts the main elements in a response', function (): void {
    // Act
    $count = mainLandmarkCount(get(route('about')));

    // Assert
    expect($count)->toBe(1);
});

test('responseDocument parses the response body as HTML', function (): void {
    // Act
    $document = responseDocument(get(route('about')));

    // Assert
    expect($document->querySelectorAll('h1'))->toHaveCount(1);
});

test('assertCreateFormErrors fills and submits the create form', function (): void {
    // Arrange
    Guide::factory()->create(['slug' => 'existing-guide']);
    actingAsAdmin();

    // Act and assert
    assertCreateFormErrors(CreateGuide::class, [
        'title' => 'Sample guide',
        'slug' => 'existing-guide',
        'category' => GuideCategory::cases()[0],
        'content' => 'Draft text',
    ], ['slug' => 'unique']);
});

test('assertListShowsReadinessAndStatus finds both on the resource listing', function (): void {
    // Arrange
    actingAsAdmin();

    // Act and assert
    assertListShowsReadinessAndStatus(GuideResource::getUrl(), Guide::class);
});

test('assertListTabsFilterDraftsAndScheduled checks both tabs on every content listing', function (): void {
    // Arrange
    actingAsAdmin();

    // Act and assert
    assertListTabsFilterDraftsAndScheduled(ListPosts::class, Post::class);
    assertListTabsFilterDraftsAndScheduled(ListGuides::class, Guide::class);
    assertListTabsFilterDraftsAndScheduled(ListEpisodes::class, Episode::class);
});

test('assertListHeaderCountsOnlyPublished ignores scheduled records on every content listing', function (): void {
    // Arrange
    actingAsAdmin();

    // Act and assert
    assertListHeaderCountsOnlyPublished(ListPosts::class, Post::class);
    assertListHeaderCountsOnlyPublished(ListGuides::class, Guide::class);
    assertListHeaderCountsOnlyPublished(ListEpisodes::class, Episode::class);
});

test('assertEditKeepsSlug saves the form without changing a published slug', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()
        ->credited()
        ->create(['slug' => 'permanent-url']);

    // Act and assert
    assertEditKeepsSlug(EditGuide::class, $guide, ['slug' => 'replacement-url']);
});

test('assertEditRejectsInvalidDraftSlug reports the slug pattern error', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()
        ->draft()
        ->create();

    // Act and assert
    assertEditRejectsInvalidDraftSlug(EditGuide::class, $guide);
});

test('assertEditOffersDraftPreview checks the signed preview link for every content type', function (): void {
    // Arrange
    actingAsAdmin();

    // Act and assert
    assertEditOffersDraftPreview(EditPost::class, Post::factory()
        ->draft()
        ->create(), 'preview.post');
    assertEditOffersDraftPreview(EditGuide::class, Guide::factory()
        ->draft()
        ->create(), 'preview.guide');
    assertEditOffersDraftPreview(EditEpisode::class, Episode::factory()
        ->draft()
        ->create(), 'preview.episode');
});

test('assertEditPublishes publishes a ready draft', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()
        ->draft()
        ->credited()
        ->create();

    // Act and assert
    assertEditPublishes(EditGuide::class, $guide, 'Guide published');
});

test('assertEditUnpublishes returns published content to draft', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()->create();

    // Act and assert
    assertEditUnpublishes(EditGuide::class, $guide, 'Guide unpublished');
});

test('assertEditRestoresDeleted deletes and restores the public page', function (): void {
    // Arrange
    actingAsAdmin();
    $episode = Episode::factory()->create();

    // Act and assert
    assertEditRestoresDeleted(EditEpisode::class, $episode, route('episodes.show', $episode));
});

test('assertEditSavesSeo saves the SEO title and description', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()
        ->credited()
        ->create();

    // Act and assert
    assertEditSavesSeo(EditGuide::class, $guide);
});

test('assertEditReplacesCover stores the new cover and removes the old one', function (): void {
    // Arrange
    actingAsAdmin();
    $guide = Guide::factory()
        ->credited()
        ->create();

    // Act and assert
    assertEditReplacesCover(EditGuide::class, $guide, 'guides');
});
