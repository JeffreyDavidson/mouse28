<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\SiteNavigationPlacement;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The public site's navigation links, defined once and rendered in each place they appear.
 */
class SiteNavigation extends Component
{
    public SiteNavigationPlacement $placement;

    public function __construct(string $placement)
    {
        $this->placement = SiteNavigationPlacement::from($placement);
    }

    /**
     * @return list<array{label: string, href: string, active: bool, isSearch: bool}>
     */
    public function links(): array
    {
        return array_map(
            fn (array $link): array => [
                'label' => $link['label'],
                'href' => route($link['route']),
                'active' => request()->routeIs($link['active'] ?? $link['route']),
                'isSearch' => $link['route'] === 'search',
            ],
            $this->definitions(),
        );
    }

    public function render(): View
    {
        return view('components.site-navigation');
    }

    /**
     * @return list<array{label: string, route: string, active?: string}>
     */
    private function definitions(): array
    {
        return match ($this->placement) {
            SiteNavigationPlacement::Desktop,
            SiteNavigationPlacement::Noscript,
            SiteNavigationPlacement::Mobile => [
                ['label' => 'Home', 'route' => 'home'],
                ['label' => 'Blog', 'route' => 'blog.index', 'active' => 'blog.*'],
                ['label' => 'Podcast', 'route' => 'episodes.index', 'active' => 'episodes.*'],
                ['label' => 'About', 'route' => 'about'],
                ['label' => 'Contact', 'route' => 'contact.create', 'active' => 'contact.*'],
                ['label' => 'Search', 'route' => 'search'],
            ],
            SiteNavigationPlacement::FooterExplore => [
                ['label' => 'Blog', 'route' => 'blog.index', 'active' => 'blog.*'],
                ...$this->guidesLink(),
                ['label' => 'Podcast', 'route' => 'episodes.index', 'active' => 'episodes.*'],
                ['label' => 'About Us', 'route' => 'about'],
            ],
            SiteNavigationPlacement::FooterConnect => [
                ['label' => 'Contact Us', 'route' => 'contact.create', 'active' => 'contact.*'],
                ['label' => 'Privacy', 'route' => 'privacy'],
            ],
            SiteNavigationPlacement::Recovery => [
                ['label' => 'Blog', 'route' => 'blog.index', 'active' => 'blog.*'],
                ...$this->guidesLink(),
                ['label' => 'Podcast', 'route' => 'episodes.index', 'active' => 'episodes.*'],
            ],
        };
    }

    /**
     * Guides stay out of the navigation until the feature flag is on.
     *
     * @return list<array{label: string, route: string, active?: string}>
     */
    private function guidesLink(): array
    {
        if (! config('mouse28.guides_enabled')) {
            return [];
        }

        return [['label' => 'Guides', 'route' => 'guides.index', 'active' => 'guides.*']];
    }
}
