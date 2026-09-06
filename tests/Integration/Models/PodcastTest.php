<?php

use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('podcast info provides site defaults without a settings record', function (): void {
    $podcast = Podcast::info();

    expect($podcast->exists)->toBeFalse()
        ->and($podcast->name)->toBe('Mouse28')
        ->and($podcast->description)->toBe('Disney parks through the lens of raising a daughter with autism.');
});

test('podcast settings persist one reusable settings record', function (): void {
    $settings = Podcast::settings();
    $settings->update(['name' => 'Mouse28 Weekly']);

    $retrievedSettings = Podcast::settings();

    expect($retrievedSettings->id)->toBe($settings->id)
        ->and($retrievedSettings->name)->toBe('Mouse28 Weekly')
        ->and(Podcast::query()->count())->toBe(1);
});
