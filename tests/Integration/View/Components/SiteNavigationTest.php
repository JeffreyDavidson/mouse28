<?php

use App\Enums\SiteNavigationPlacement;
use App\View\Components\SiteNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

function visitRoute(string $name): void
{
    $request = Request::create(route($name));
    $request->setRouteResolver(fn () => Route::getRoutes()->match($request));

    app()->instance('request', $request);
}

/**
 * @return list<array<string, string>>
 */
function navigationLinks(SiteNavigationPlacement $placement): array
{
    return array_map(
        fn (array $link): array => [$link['label'] => $link['href']],
        (new SiteNavigation($placement->value))->links(),
    );
}

test('the primary navigation lists the same links in the desktop, noscript and mobile menus', function (): void {
    $expected = [
        ['Home' => route('home')],
        ['Blog' => route('blog.index')],
        ['Podcast' => route('episodes.index')],
        ['About' => route('about')],
        ['Contact' => route('contact.create')],
        ['Search' => route('search')],
    ];

    expect(navigationLinks(SiteNavigationPlacement::Desktop))->toBe($expected);
    expect(navigationLinks(SiteNavigationPlacement::Noscript))->toBe($expected);
    expect(navigationLinks(SiteNavigationPlacement::Mobile))->toBe($expected);
});

test('the footer lists its own explore and connect links', function (): void {
    config()->set('mouse28.guides_enabled', false);

    expect(navigationLinks(SiteNavigationPlacement::FooterExplore))->toBe([
        ['Blog' => route('blog.index')],
        ['Podcast' => route('episodes.index')],
        ['About Us' => route('about')],
    ]);
    expect(navigationLinks(SiteNavigationPlacement::FooterConnect))->toBe([
        ['Contact Us' => route('contact.create')],
        ['Privacy' => route('privacy')],
    ]);
});

test('the recovery navigation keeps the error page link set', function (): void {
    config()->set('mouse28.guides_enabled', false);

    expect(navigationLinks(SiteNavigationPlacement::Recovery))->toBe([
        ['Blog' => route('blog.index')],
        ['Podcast' => route('episodes.index')],
    ]);
});

test('guides appear in the footer and recovery navigation only when enabled', function (SiteNavigationPlacement $placement): void {
    config()->set('mouse28.guides_enabled', true);

    expect(navigationLinks($placement))->toContain(['Guides' => route('guides.index')]);
})->with([
    'footer explore' => SiteNavigationPlacement::FooterExplore,
    'recovery' => SiteNavigationPlacement::Recovery,
]);

test('guides are left out of the footer and recovery navigation while disabled', function (SiteNavigationPlacement $placement): void {
    config()->set('mouse28.guides_enabled', false);

    expect(navigationLinks($placement))->not->toContain(['Guides' => route('guides.index')]);
})->with([
    'footer explore' => SiteNavigationPlacement::FooterExplore,
    'recovery' => SiteNavigationPlacement::Recovery,
]);

test('a link is active on its own route and on its section routes', function (string $routeName, string $activeLabel): void {
    visitRoute($routeName);

    $active = collect((new SiteNavigation('mobile'))->links())
        ->where('active', true)
        ->pluck('label')
        ->all();

    expect($active)->toBe([$activeLabel]);
})->with([
    'home' => ['home', 'Home'],
    'blog index' => ['blog.index', 'Blog'],
    'podcast index' => ['episodes.index', 'Podcast'],
    'about' => ['about', 'About'],
    'contact form' => ['contact.create', 'Contact'],
    'search' => ['search', 'Search'],
]);

test('the desktop and mobile menus mark the current page while the noscript menu does not', function (): void {
    visitRoute('about');

    $desktop = Blade::render('<x-site-navigation placement="desktop" />');
    $mobile = Blade::render('<x-site-navigation placement="mobile" />');
    $noscript = Blade::render('<x-site-navigation placement="noscript" />');

    expect(substr_count($desktop, 'aria-current="page"'))->toBe(1);
    expect($desktop)->toContain('dispatch-nav-link');
    expect($desktop)->toContain('aria-label="Search Mouse28"');
    expect(substr_count($mobile, 'aria-current="page"'))->toBe(1);
    expect($mobile)->toContain('text-gold bg-white/5');
    expect($noscript)->not->toContain('aria-current');
});

test('footer and recovery links render without a current page marker', function (string $placement): void {
    visitRoute('blog.index');

    $html = Blade::render("<x-site-navigation placement=\"{$placement}\" />");

    expect($html)->toContain('hover:text-gold');
    expect($html)->not->toContain('aria-current');
})->with(['footer-explore', 'footer-connect', 'recovery']);

test('an unknown placement is rejected', function (): void {
    new SiteNavigation('sidebar');
})->throws(ValueError::class);
