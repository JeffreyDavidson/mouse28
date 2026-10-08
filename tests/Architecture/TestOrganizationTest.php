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
        'Config/ObservabilityTest.php' => 'config/newdebugbar.php',
        'Config/SentryTest.php' => 'config/sentry.php',
        'Config/Mouse28Test.php' => 'config/mouse28.php',
        'Http/ExceptionHandlingTest.php' => 'bootstrap/app.php',
        'HealthTest.php' => 'app/Providers/AppServiceProvider.php',
        'Database/SampleContentSeederTest.php' => 'database/seeders/SampleContentSeeder.php',
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
        'Config/MediaTest.php' => 'config/media.php',
        'Http/ProductionSmokeTest.php' => 'routes/web.php',
        'Support/DeploymentSmokeClientTest.php' => 'tests/Support/DeploymentSmokeClient.php',
        'PublicPageSeoTagsTest.php' => 'routes/web.php',
        'View/Layouts/AppLayoutTest.php' => 'resources/views/components/layouts/app.blade.php',
        'View/Layouts/ErrorLayoutTest.php' => 'resources/views/components/layouts/error.blade.php',
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
