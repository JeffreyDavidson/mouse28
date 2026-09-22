<?php

use App\Support\ErrorRecovery;
use Illuminate\Http\Request;

test('error recovery uses safe form destinations instead of untrusted referrers', function (string $path, string $route, string $fragment): void {
    $request = Request::create($path, 'POST', server: ['HTTP_REFERER' => 'https://external.example.test/steal']);

    expect(ErrorRecovery::for($request, 500)['url'])->toBe(route($route).$fragment);
})->with([
    'contact' => ['/contact', 'contact.show', ''],
    'newsletter' => ['/newsletter', 'home', '#newsletter'],
    'unknown POST' => ['/unknown-post', 'home', ''],
    'Livewire POST' => ['/livewire/update', 'home', ''],
]);
