<?php

declare(strict_types=1);

test('tests use Pest files within a registered suite', function (): void {
    $testRoot = dirname(__DIR__);
    $registeredSuites = ['Arch', 'Browser', 'Feature', 'Integration', 'Unit'];
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = str_replace($testRoot.DIRECTORY_SEPARATOR, '', $file->getPathname());
        if (in_array($relativePath, ['BrowserTestCase.php', 'Pest.php', 'TestCase.php'], true)) {
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
        'Config/SentryTest.php' => 'config/sentry.php',
        'Http/Routes/AboutTest.php' => 'routes/web.php',
        'Views/Errors/Error404Test.php' => 'resources/views/errors/404.blade.php',
        'Views/Errors/Error419Test.php' => 'resources/views/errors/419.blade.php',
        'Views/Errors/Error500Test.php' => 'resources/views/errors/500.blade.php',
        'Views/Errors/Error503Test.php' => 'resources/views/errors/503.blade.php',
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
