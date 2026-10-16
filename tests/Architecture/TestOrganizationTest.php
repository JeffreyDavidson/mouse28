<?php

declare(strict_types=1);

test('tests use Pest files within a registered suite', function (): void {
    $testRoot = dirname(__DIR__);
    $registeredSuites = ['Architecture', 'Browser', 'Feature', 'Integration', 'Unit'];
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = str_replace($testRoot.DIRECTORY_SEPARATOR, '', $file->getPathname());
        if (in_array($relativePath, ['BrowserTestCase.php', 'Pest.php', 'TestCase.php', 'Support/DeploymentSmokeClient.php'], true)) {
            continue;
        }

        $suite = explode(DIRECTORY_SEPARATOR, $relativePath)[0];
        if (! in_array($suite, $registeredSuites, true)) {
            $violations[] = "{$relativePath}: test is outside a registered suite";
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new RuntimeException("Unable to read {$relativePath}.");
        }

        if (preg_match('/\bclass\s+\w+Test\s+extends\b/', $contents) === 1) {
            $violations[] = "{$relativePath}: use Pest functions instead of a PHPUnit test class";
        }
    }

    expect($violations)->toBeEmpty(implode("\n", $violations));
});

test('test paths mirror their application source', function (string $suite): void {
    $projectRoot = dirname(__DIR__, 2);
    $suiteRoot = $projectRoot.'/tests/'.$suite.'/';
    $nonClassSources = [
        'TestHarnessTest.php' => 'tests/TestCase.php',
        'PestHelpersTest.php' => 'tests/Pest.php',
        'Scripts/CheckMethodChainingTest.php' => 'scripts/check-method-chaining.php',
        'ConsoleScheduleTest.php' => 'routes/console.php',
        'AboutTest.php' => 'routes/web.php',
        'PrivacyTest.php' => 'routes/web.php',
        'LegacyContactInquiryLinkTest.php' => 'routes/web.php',
        'Config/ObservabilityTest.php' => 'config/newdebugbar.php',
        'Config/SentryTest.php' => 'config/sentry.php',
        'Config/Mouse28Test.php' => 'config/mouse28.php',
        'Http/ExceptionHandlingTest.php' => 'bootstrap/app.php',
        'HealthTest.php' => 'app/Providers/AppServiceProvider.php',
        'Database/SampleContentSeederTest.php' => 'database/seeders/SampleContentSeeder.php',
        // Kept for classes that moved to creator-kit: these run Mouse28's own models and admin pages.
        'Enums/PublishStatusTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Enums/PublishStatus.php',
        'Models/Concerns/HasDraftScopeTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Models/Concerns/HasDraftScope.php',
        'Models/Concerns/HasPublishingStatusTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Models/Concerns/HasPublishingStatus.php',
        'Models/Concerns/LocksSlugAfterPublicationTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Models/Concerns/LocksSlugAfterPublication.php',
        'Filament/Actions/PublishContentActionTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Actions/PublishContentAction.php',
        'Filament/Forms/Components/PublishDatePickerTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Forms/Components/PublishDatePicker.php',
        'Filament/Forms/Components/PublishStatusSelectTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Forms/Components/PublishStatusSelect.php',
        'Filament/Forms/Components/SlugInputTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Forms/Components/SlugInput.php',
        'Support/DisplayTimezoneTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Support/Time/DisplayTimezone.php',
        'Support/TextSearchTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Support/Search/TextSearch.php',
        'Models/Concerns/ScopesMissingSeoTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Models/Concerns/ScopesMissingSeo.php',
        'Filament/Tables/Filters/MissingSeoFilterTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Tables/Filters/MissingSeoFilter.php',
        'Filament/Forms/Components/SeoSectionTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Forms/Components/SeoSection.php',
        'Http/Middleware/EnsureValidNewsletterConfirmationLinkTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Http/Middleware/EnsureValidNewsletterConfirmationLink.php',
        'Http/Controllers/ResendWebhookControllerTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Http/Controllers/ResendWebhookController.php',
        'Http/Controllers/NewsletterOneClickUnsubscriptionControllerTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Http/Controllers/NewsletterOneClickUnsubscriptionController.php',
        'Enums/SubscriberStatusTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Enums/SubscriberStatus.php',
        'Support/Newsletter/UnsubscribeUrlGeneratorTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Presenters/SubscriberPresenter.php',
        'Actions/ConfirmNewsletterSubscriptionTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/ConfirmNewsletterSubscription.php',
        'Actions/HandleResendWebhookTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/HandleResendWebhook.php',
        'Actions/RequestNewsletterSubscriptionTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/RequestNewsletterSubscription.php',
        'Actions/SendNewsletterIssueTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/SendNewsletterIssue.php',
        'Actions/SendNewsletterIssueTestEmailTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/SendNewsletterIssueTestEmail.php',
        'Actions/SuppressSubscriberTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/SuppressSubscriber.php',
        'Actions/UnsubscribeFromNewsletterTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/UnsubscribeFromNewsletter.php',
        'Jobs/DeliverNewsletterIssueTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Jobs/DeliverNewsletterIssue.php',
        'Actions/SendContactMessageTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Actions/SendContactMessage.php',
        'Config/CreatorKitContactTest.php' => 'config/creator-kit.php',
        'Filament/Forms/Components/SlugSourceInputTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Forms/Components/SlugSourceInput.php',
        'Filament/Resources/Categories/CategoryResourceTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/Categories/CategoryResource.php',
        'Filament/Resources/ContactInquiries/ContactInquiryResourceTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/ContactInquiries/ContactInquiryResource.php',
        'Filament/Resources/ContactInquiries/Pages/EditContactInquiryTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/ContactInquiries/Pages/EditContactInquiry.php',
        'Filament/Resources/ContactInquiries/Pages/ListContactInquiriesTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/ContactInquiries/Pages/ListContactInquiries.php',
        'Filament/Resources/ContactInquiries/Tables/ContactInquiriesTableTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/ContactInquiries/Tables/ContactInquiriesTable.php',
        'Filament/Actions/ReplyToInquiryActionTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Actions/ReplyToInquiryAction.php',
        'Filament/Resources/NewsletterIssues/Pages/CreateNewsletterIssueTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/NewsletterIssues/Pages/CreateNewsletterIssue.php',
        'Filament/Resources/NewsletterIssues/Pages/EditNewsletterIssueTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/NewsletterIssues/Pages/EditNewsletterIssue.php',
        'Filament/Resources/NewsletterIssues/Pages/ListNewsletterIssuesTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/NewsletterIssues/Pages/ListNewsletterIssues.php',
        'Filament/Resources/Subscribers/SubscriberResourceTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/Subscribers/SubscriberResource.php',
        'Filament/Resources/Categories/Pages/CreateCategoryTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/Categories/Pages/CreateCategory.php',
        'Filament/Resources/Categories/Pages/EditCategoryTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/Categories/Pages/EditCategory.php',
        'Filament/Resources/Categories/Pages/ListCategoriesTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/Categories/Pages/ListCategories.php',
        'Filament/Resources/SocialProfiles/SocialProfileResourceTest.php' => 'vendor/jeffreydavidson/creator-kit/src/Filament/Resources/SocialProfiles/SocialProfileResource.php',
        'Database/CategorySeederTest.php' => 'database/seeders/CategorySeeder.php',
        'Database/Migrations/CopyContactMessagesToContactInquiriesTest.php' => 'database/migrations/2026_10_02_164233_copy_contact_messages_to_contact_inquiries.php',
        'Database/Migrations/CreateSocialProfilesTableTest.php' => 'database/migrations/2026_10_02_173351_create_social_profiles_table.php',
        'Database/Migrations/AddPublishStatusToEditorialContentTest.php' => 'database/migrations/2026_10_02_195819_add_publish_status_to_editorial_content.php',
        'Database/Migrations/AddContentToPostsAndGuidesTest.php' => 'database/migrations/2026_10_02_212427_add_content_to_posts_and_guides.php',
        'Database/Migrations/CreateEpisodePostTableTest.php' => 'database/migrations/2026_10_02_235304_create_episode_post_table.php',
        'Database/Migrations/CopyPostEpisodeLinksToEpisodePostTest.php' => 'database/migrations/2026_10_02_235306_copy_post_episode_links_to_episode_post.php',
        'Database/Migrations/CreateCategoriesTableTest.php' => 'database/migrations/2026_10_03_015027_create_categories_table.php',
        'Database/Migrations/AddCategoryIdToPostsTableTest.php' => 'database/migrations/2026_10_03_015030_add_category_id_to_posts_table.php',
        'Database/Migrations/CopyPostCategoriesToCategoriesTest.php' => 'database/migrations/2026_10_03_015033_copy_post_categories_to_categories.php',
        'Database/Migrations/AddAuthorFieldsToUsersTableTest.php' => 'database/migrations/2026_10_03_144331_add_author_fields_to_users_table.php',
        'Database/Migrations/CreatePostUserAndGuideUserTablesTest.php' => 'database/migrations/2026_10_03_144332_create_post_user_and_guide_user_tables.php',
        'Database/Migrations/CopyContentAuthorsToAuthorPivotsTest.php' => 'database/migrations/2026_10_03_144333_copy_content_authors_to_author_pivots.php',
        'Database/Migrations/AddStoredMediaPathsToContentTablesTest.php' => 'database/migrations/2026_10_03_215858_add_stored_media_paths_to_content_tables.php',
        'Database/Migrations/DropLegacyCoverImageColumnsTest.php' => 'database/migrations/2026_10_04_221212_drop_legacy_cover_image_columns.php',
        'Database/Migrations/DropLegacyPublishedAndBodyColumnsTest.php' => 'database/migrations/2026_10_05_143704_drop_legacy_published_and_body_columns.php',
        'Database/Migrations/DropLegacyPostLinkCategoryAndAuthorColumnsTest.php' => 'database/migrations/2026_10_05_150608_drop_legacy_post_link_category_and_author_columns.php',
        'Database/Migrations/BackfillSeoFromMetaColumnsTest.php' => 'database/migrations/2026_10_05_174142_backfill_seo_from_meta_columns.php',
        'Database/Migrations/AddPodcastShapeTest.php' => 'database/migrations/2026_10_06_231450_add_podcast_and_guest_fields_to_episodes_table.php',
        'Database/Migrations/DropLegacyMetaColumnsTest.php' => 'database/migrations/2026_10_06_003047_drop_legacy_meta_columns.php',
        'Database/Migrations/AddReviewFieldsToPostsTableTest.php' => 'database/migrations/2026_10_07_222557_add_review_fields_to_posts_table.php',
        'Database/Migrations/DropLegacyEpisodeMediaAndPodcastSocialColumnsTest.php' => 'database/migrations/2026_10_07_031500_drop_legacy_episode_media_and_podcast_social_columns.php',
        'Config/MediaTest.php' => 'config/creator-kit.php',
        'Config/CreatorKitNewsletterTest.php' => 'config/creator-kit.php',
        // The package's commands, run against Mouse28's models and its list in config/creator-kit.php.
        'Console/Commands/RepairResponsiveImagesTest.php' => 'config/creator-kit.php',
        'Console/Commands/VerifyResponsiveImagesTest.php' => 'config/creator-kit.php',
        'Config/SearchTest.php' => 'config/search.php',
        'Config/DatabaseTest.php' => 'config/database.php',
        'Http/ProductionSmokeTest.php' => 'routes/web.php',
        'Support/DeploymentSmokeClientTest.php' => 'tests/Support/DeploymentSmokeClient.php',
        'PublicPageSeoTagsTest.php' => 'routes/web.php',
        'View/Layouts/AppLayoutTest.php' => 'resources/views/components/layouts/app.blade.php',
        'View/Layouts/ErrorLayoutTest.php' => 'resources/views/components/layouts/error.blade.php',
        'PublicPageConventionsTest.php' => 'routes/web.php',
    ];
    $violations = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($suiteRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! str_ends_with($file->getFilename(), 'Test.php')) {
            continue;
        }

        $relativePath = substr($file->getPathname(), strlen($suiteRoot));
        $source = $nonClassSources[$relativePath] ?? 'app/'.substr($relativePath, 0, -8).'.php';

        if (! is_file($projectRoot.'/'.$source)) {
            $violations[] = "{$relativePath}: expected source {$source}";
        }
    }

    expect($violations)->toBeEmpty(implode("\n", $violations));
})->with(['Feature', 'Integration', 'Unit']);
