<?php

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\RequestWatcher;

test('failed request recording redacts contact fields and flashed input', function (): void {
    $request = Request::create('/testing/failure', 'POST', [
        'name' => 'Private Name', 'email' => 'private@example.test', 'subject' => 'Private Subject',
        'message' => 'Private Message', 'cf-turnstile-response' => 'private-token',
    ]);
    $session = app('session.store');
    $session->put('_old_input', ['message' => 'Private Flash']);
    $request->setLaravelSession($session);
    Telescope::$entriesQueue = [];
    Telescope::startRecording();

    try {
        (new RequestWatcher)->recordRequest(new RequestHandled($request, new Response('', 500)));

        expect(Telescope::$entriesQueue)->toHaveCount(1);
        $recorded = json_encode(Telescope::$entriesQueue, JSON_THROW_ON_ERROR);
        expect($recorded)->not->toContain('Private Name', 'private@example.test', 'Private Subject', 'Private Message', 'private-token', 'Private Flash');
    } finally {
        Telescope::stopRecording();
        Telescope::$entriesQueue = [];
    }
});

test('sensitive form batches are excluded even when exceptions contain submitted values', function (string $path, bool $allowed): void {
    $this->app->instance('request', Request::create($path, 'POST'));
    Illuminate\Support\Facades\Request::clearResolvedInstance('request');

    expect(collect(Telescope::$filterBatchUsing)->every(fn (Closure $filter): bool => $filter(collect()) === true))
        ->toBe($allowed);
})->with([
    'contact' => ['/contact', false],
    'newsletter' => ['/newsletter', false],
    'unrelated request' => ['/other', true],
]);

test('the application registers the Telescope service provider', function (): void {
    expect($this->app->getProvider(TelescopeServiceProvider::class))
        ->toBeInstanceOf(TelescopeServiceProvider::class);
});

test('only administrators may view Telescope', function (): void {
    $administrator = User::factory()->admin()->make();
    $editor = User::factory()->make();

    expect(Gate::forUser($administrator)->allows('viewTelescope'))->toBeTrue()
        ->and(Gate::forUser($editor)->allows('viewTelescope'))->toBeFalse();
});
