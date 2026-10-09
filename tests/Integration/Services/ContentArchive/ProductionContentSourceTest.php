<?php

use App\Services\ContentArchive\ProductionContentSource;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

covers(ProductionContentSource::class);

beforeEach(function (): void {
    config()->set('mouse28.production_sync.ssh_host', 'forge@cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current/');
});

/** @return list<string> */
function productionSourceArguments(PendingProcess $process): array
{
    $command = $process->command;

    if (! is_array($command)) {
        throw new UnexpectedValueException('The production source must pass an argument array to the process.');
    }

    return array_values(array_map(strval(...), $command));
}

test('exporting runs the production export, downloads the archive and removes the remote copy', function (): void {
    $ran = [];
    Process::fake(function (PendingProcess $process) use (&$ran) {
        $ran[] = productionSourceArguments($process);

        return Process::result();
    });
    Process::preventStrayProcesses();

    app(ProductionContentSource::class)->exportTo('/tmp/local/content.json');

    $remoteArchive = $ran[0][9] ?? '';
    expect($ran)->toHaveCount(3)
        ->and($ran[0])
        ->toBe(['ssh', '-o', 'BatchMode=yes', '-o', 'ConnectTimeout=10', 'forge@cold-moon', 'php', '/home/forge/mouse28.com/current/artisan', 'content:export-public', $remoteArchive, '--no-interaction'])
        ->and($remoteArchive)
        ->toMatch('#\A/tmp/mouse28-public-content-[0-9a-f-]{36}\.json\z#')
        ->and($ran[1])
        ->toBe(['scp', '-o', 'BatchMode=yes', '-o', 'ConnectTimeout=10', "forge@cold-moon:{$remoteArchive}", '/tmp/local/content.json'])
        ->and($ran[2])
        ->toBe(['ssh', '-o', 'BatchMode=yes', '-o', 'ConnectTimeout=10', 'forge@cold-moon', 'rm', '-f', '--', $remoteArchive]);
});

test('a failed production export stops before downloading anything', function (): void {
    Process::fake(fn (PendingProcess $process) => productionSourceArguments($process)[0] === 'ssh'
        ? Process::result(errorOutput: 'php: command not found', exitCode: 127)
        : Process::result());

    expect(fn () => app(ProductionContentSource::class)->exportTo('/tmp/local/content.json'))
        ->toThrow(RuntimeException::class, 'Production content export failed. php: command not found');

    Process::assertRanTimes(fn (PendingProcess $process): bool => true, 1);
});

test('a failed download still removes the remote archive', function (): void {
    Process::fake(fn (PendingProcess $process) => productionSourceArguments($process)[0] === 'scp'
        ? Process::result(exitCode: 1)
        : Process::result());

    expect(fn () => app(ProductionContentSource::class)->exportTo('/tmp/local/content.json'))
        ->toThrow(RuntimeException::class, 'Production content download failed.');

    Process::assertRan(fn (PendingProcess $process): bool => array_slice(productionSourceArguments($process), 5, 2) === ['forge@cold-moon', 'rm']);
});

test('an unsafe production source is refused before anything runs', function (string $host, string $sitePath): void {
    config()->set('mouse28.production_sync.ssh_host', $host);
    config()->set('mouse28.production_sync.site_path', $sitePath);
    Process::fake();

    expect(fn () => app(ProductionContentSource::class)->exportTo('/tmp/local/content.json'))
        ->toThrow(RuntimeException::class, 'Production sync source configuration is invalid.')
        ->and(fn () => app(ProductionContentSource::class)->copyMedia(['posts/cover.webp']))
        ->toThrow(RuntimeException::class, 'Production sync source configuration is invalid.');

    Process::assertNothingRan();
})->with([
    'a host that looks like an option' => ['-oProxyCommand=evil', '/home/forge/mouse28.com/current'],
    'a host with spaces' => ['cold moon', '/home/forge/mouse28.com/current'],
    'a relative site path' => ['cold-moon', 'home/forge/mouse28.com'],
    'a site path that climbs out' => ['cold-moon', '/home/forge/../root'],
    'a site path with a shell character' => ['cold-moon', '/home/forge/mouse28.com;rm'],
]);

test('copying media fetches exactly the listed public files from production storage', function (): void {
    Storage::fake('public');
    $manifest = null;
    Process::fake(function (PendingProcess $process) use (&$manifest) {
        $arguments = productionSourceArguments($process);
        $manifest = file_get_contents(substr($arguments[4], strlen('--files-from=')));

        return Process::result();
    });
    Process::preventStrayProcesses();

    app(ProductionContentSource::class)->copyMedia(['posts/cover.webp', 'podcasts/show.webp']);

    Process::assertRan(fn (PendingProcess $process): bool => array_slice(productionSourceArguments($process), 0, 4) === ['rsync', '--archive', '--relative', '--rsh=ssh -o BatchMode=yes -o ConnectTimeout=10']
        && array_slice(productionSourceArguments($process), 5) === ['forge@cold-moon:/home/forge/mouse28.com/current/storage/app/public/', Storage::disk('public')->path('')]);
    expect($manifest)->toBe("posts/cover.webp\npodcasts/show.webp\n");
});

test('copying no media runs nothing', function (): void {
    Process::fake();

    app(ProductionContentSource::class)->copyMedia([]);

    Process::assertNothingRan();
});

test('a failed media copy is reported', function (): void {
    Storage::fake('public');
    Process::fake(fn () => Process::result(errorOutput: 'connection reset', exitCode: 23));

    expect(fn () => app(ProductionContentSource::class)->copyMedia(['posts/cover.webp']))
        ->toThrow(RuntimeException::class, 'Production media download failed. connection reset');
});
