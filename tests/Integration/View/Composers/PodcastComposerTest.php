<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ViewErrorBag;

pest()->use(RefreshDatabase::class);

test('the application layout composer shares the podcast links with its view', function (): void {
    $view = view('components.layouts.app', [
        'errors' => new ViewErrorBag,
        'slot' => new HtmlString(''),
    ]);

    app('view')->callComposer($view);
    $data = $view->getData();

    expect($data)->toHaveKey('podcastLinks')
        ->not->toHaveKey('podcast');
});
