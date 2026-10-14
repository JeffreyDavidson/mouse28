<?php

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('repository scans skip nested worktrees while still detecting application violations', function (string $worktreeDirectory): void {
    $filesystem = new Filesystem;
    $reservedPath = tempnam(sys_get_temp_dir(), 'mouse28-chain-scan-');
    if ($reservedPath === false) {
        throw new RuntimeException('Could not reserve a test directory.');
    }
    unlink($reservedPath);
    $root = $reservedPath;

    try {
        $filesystem->makeDirectory("{$root}/scripts", 0755, true);
        $filesystem->makeDirectory("{$root}/app", 0755, true);
        $filesystem->makeDirectory("{$root}/{$worktreeDirectory}/other/app", 0755, true);
        $filesystem->copy(base_path('scripts/check-method-chaining.php'), "{$root}/scripts/check-method-chaining.php");
        $violation = '<?php $query->where("a", 1)->first();';
        $filesystem->put("{$root}/app/Violation.php", $violation);
        $filesystem->put("{$root}/{$worktreeDirectory}/other/app/Violation.php", $violation);

        $process = new Process([PHP_BINARY, 'scripts/check-method-chaining.php', '.'], $root);
        $process->run();

        expect($process->getExitCode())->toBe(1)
            ->and($process->getErrorOutput())
            ->toContain('app/Violation.php:1 chains more than one call on one line.')
            ->not->toContain($worktreeDirectory);
    } finally {
        $filesystem->deleteDirectory($root);
    }
})->with([
    'Claude worktrees' => '.claude/worktrees',
    'Codex worktrees' => '.codex/worktrees',
    'local worktrees' => '.worktrees',
]);
