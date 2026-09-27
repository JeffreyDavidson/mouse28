<?php

declare(strict_types=1);

/**
 * @return array<string, list<string>> rule file path => globs
 */
function indexedRuleFiles(): array
{
    $index = (string) file_get_contents(dirname(__DIR__, 2).'/.ai/rules/index.md');
    $rows = [];

    foreach (explode("\n", $index) as $line) {
        if (preg_match('/^\| (.+) \| (\.ai\/rules\/\S+\.md) \|$/', $line, $matches) === 1) {
            $rows[$matches[2]] = explode(', ', $matches[1]);
        }
    }

    return $rows;
}

/**
 * @return list<string>
 */
function ruleFrontMatterPaths(string $file): array
{
    $contents = (string) file_get_contents($file);

    if (preg_match('/\A---\n(.*?)\n---\n/s', $contents, $frontMatter) !== 1) {
        return [];
    }

    preg_match_all("/^\\s*-\\s+'?([^'\\n]+?)'?\\s*$/m", $frontMatter[1], $paths);

    return $paths[1];
}

test('every rule file is listed in the rules index', function (): void {
    $root = dirname(__DIR__, 2);
    $ruleFiles = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/.ai/rules", FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file instanceof SplFileInfo && $file->getExtension() === 'md' && $file->getFilename() !== 'index.md') {
            $ruleFiles[] = substr($file->getPathname(), strlen("{$root}/"));
        }
    }

    sort($ruleFiles);
    $indexed = array_keys(indexedRuleFiles());
    sort($indexed);

    expect($indexed)->toBe($ruleFiles);
});

test('each rules index row matches its file path globs', function (): void {
    $root = dirname(__DIR__, 2);

    foreach (indexedRuleFiles() as $ruleFile => $globs) {
        $paths = ruleFrontMatterPaths("{$root}/{$ruleFile}");
        sort($paths);
        sort($globs);

        expect($globs)->toBe($paths, "{$ruleFile} index globs differ from its front matter");
    }
});
