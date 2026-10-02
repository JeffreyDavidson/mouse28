<?php

use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ViewErrorBag;

pest()->use(RefreshDatabase::class);

test('the application layout composer shares only enabled footer social profiles', function (): void {
    $shown = SocialProfile::factory()->create();
    SocialProfile::factory()->create(['is_enabled' => false]);
    SocialProfile::factory()->create(['show_in_footer' => false]);

    $view = view('components.layouts.app', [
        'errors' => new ViewErrorBag,
        'slot' => new HtmlString(''),
    ]);

    app('view')->callComposer($view);

    expect(Collection::wrap($view->getData()['footerSocialProfiles'])->pluck('id')->all())->toBe([$shown->id]);
});
