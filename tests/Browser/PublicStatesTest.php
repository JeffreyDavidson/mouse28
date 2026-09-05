<?php

use Illuminate\Support\Facades\Route;

function stateHorizontalOverflowCountScript(): string
{
    return <<<'JS'
        (() => document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0)()
        JS;
}

function stateUndersizedControlsScript(): string
{
    return <<<'JS'
        (() => {
            const controls = document.querySelectorAll([
                'button',
                'input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"])',
                'select',
                'textarea',
                'summary',
                'a[href]',
            ].join(','));

            return [...controls].filter((control) => {
                const styles = window.getComputedStyle(control);
                const bounds = control.getBoundingClientRect();
                const isInlineLink = control.matches('a[href]') && styles.display === 'inline';

                return ! isInlineLink
                    && ! control.closest('[aria-hidden="true"]')
                    && styles.display !== 'none'
                    && styles.visibility !== 'hidden'
                    && bounds.width > 0
                    && bounds.height > 0
                    && (bounds.width < 44 || bounds.height < 44);
            }).map((control) => {
                const bounds = control.getBoundingClientRect();
                const identity = control.id ? `#${control.id}` : control.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                return `${control.tagName.toLowerCase()}${identity} (${Math.round(bounds.width)}x${Math.round(bounds.height)})`;
            }).join('|');
        })()
        JS;
}

function stateMissingFocusIndicatorsScript(): string
{
    return <<<'JS'
        (() => {
            const focusableElements = document.querySelectorAll([
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled]):not([type="hidden"])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                'summary',
                '[tabindex]:not([tabindex="-1"])',
            ].join(','));

            return [...focusableElements].filter((element) => {
                const bounds = element.getBoundingClientRect();

                if (element.closest('[aria-hidden="true"], details:not([open])') || bounds.width === 0 || bounds.height === 0) {
                    return false;
                }

                element.focus();

                const styles = window.getComputedStyle(element);
                const hasOutline = styles.outlineStyle !== 'none'
                    && styles.outlineColor !== 'rgba(0, 0, 0, 0)'
                    && Number.parseFloat(styles.outlineWidth) > 0;
                const hasBoxShadow = styles.boxShadow !== 'none';

                return ! hasOutline && ! hasBoxShadow;
            }).map((element) => {
                const identity = element.id ? `#${element.id}` : element.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                return `${element.tagName.toLowerCase()}${identity}`;
            }).join('|');
        })()
        JS;
}

test('empty and no-result states remain actionable on mobile', function (): void {
    $pages = visit([
        route('blog.index'),
        route('blog.index', ['q' => 'quiet']),
        route('guides.index'),
        route('guides.index', ['category' => 'accessibility']),
        route('episodes.index'),
        route('search', ['q' => 'quiet']),
    ])
        ->on()
        ->mobile()
        ->resize(320, 812);

    $pages->assertScript(stateHorizontalOverflowCountScript(), 0)
        ->assertScript(stateUndersizedControlsScript(), '')
        ->assertScript(stateMissingFocusIndicatorsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    [$blog, $filteredBlog, $guides, $filteredGuides, $episodes, $search] = $pages;

    $blog->assertSee("We're putting pen to paper");
    $filteredBlog->assertSee('No posts match "quiet"')
        ->assertSee('View all posts');
    $guides->assertSee('Guides are on the way');
    $filteredGuides->assertSee('No Accessibility guides yet')
        ->assertSee('View all guides');
    $episodes->assertSee("We're warming up the mics");
    $search->assertSee('No results for “quiet”')
        ->assertSee('Browse blog')
        ->assertSee('Browse guides')
        ->assertSee('Browse podcast');
});

test('search validation identifies and focuses the invalid query', function (): void {
    $query = str_repeat('a', 101);
    $page = visit(route('search'));

    $page->script('document.querySelector("#site-search").removeAttribute("maxlength")');

    $page->fill('#site-search', $query)
        ->keys('#site-search', 'Enter')
        ->assertSee('Search terms may not be longer than 100 characters.')
        ->assertAttribute('#site-search', 'aria-invalid', 'true')
        ->assertAttribute('#site-search', 'aria-describedby', 'site-search-error')
        ->assertScript('document.activeElement.id', 'site-search')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('contact page offers an actionable email route when verification is unavailable', function (): void {
    visit(route('contact.show'))
        ->assertSee('Email us directly')
        ->assertVisible('.dispatch-letter-form a[href^="mailto:"]')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('newsletter validation and rate-limit feedback remain accessible', function (): void {
    $validationPage = visit(route('home'));

    $validationPage->script('document.querySelector(\'form[action$="/newsletter"]\').noValidate = true');

    $validationPage->fill('#home-newsletter-email', 'not-an-email')
        ->keys('form[action$="/newsletter"] button[type="submit"]', 'Enter')
        ->assertSee('The email field must be a valid email address.')
        ->assertValue('#home-newsletter-email', 'not-an-email')
        ->assertAttribute('#home-newsletter-email', 'aria-invalid', 'true')
        ->assertAttribute('#home-newsletter-email', 'aria-describedby', 'home-newsletter-email-message')
        ->assertScript('document.activeElement.id', 'home-newsletter-email')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('newsletter.store'), ['email' => 'not-an-email']);
    }

    session()->flush();

    $rateLimitPage = visit(route('home'));

    $rateLimitPage->script('document.querySelector(\'form[action$="/newsletter"]\').noValidate = true');

    $rateLimitPage->fill('#newsletter input[name="email"]', 'not-an-email')
        ->keys('form[action$="/newsletter"] button[type="submit"]', 'Enter')
        ->assertSee('Too many signup attempts. Please wait a minute and try again.')
        ->assertScript('document.querySelector("#newsletter [role=alert]") !== null')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('branded recovery pages remain accessible and actionable', function (): void {
    config()->set('app.debug', false);

    Route::get('/testing/browser-expired-session', fn () => abort(419, 'Private session details'));
    Route::get('/testing/browser-server-error', function (): never {
        throw new RuntimeException('Sensitive database connection details');
    });
    Route::get('/testing/browser-maintenance', fn () => abort(503, 'Private maintenance details'));

    $pages = visit([
        '/missing-recovery-page',
        '/testing/browser-expired-session',
        '/testing/browser-server-error',
        '/testing/browser-maintenance',
    ])
        ->on()
        ->mobile()
        ->resize(320, 812);

    $pages->assertScript(stateHorizontalOverflowCountScript(), 0)
        ->assertScript(stateUndersizedControlsScript(), '')
        ->assertScript(stateMissingFocusIndicatorsScript(), '')
        ->assertScript('document.documentElement.classList.contains("js-dispatch-errors")', true)
        ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    [$notFound, $expired, $serverError, $maintenance] = $pages;

    $notFound->assertSee('That page wandered off')
        ->assertSee('Search Mouse28')
        ->assertSee('Go home')
        ->assertScript(
            'getComputedStyle(document.querySelector(".dispatch-error-secondary")).color',
            'rgb(26, 16, 64)',
        );
    $expired->assertSee('Your session took a break')
        ->assertSee('Return to contact')
        ->assertDontSee('Private session details');
    $serverError->assertSee('The magic hit a snag')
        ->assertSee('Try again')
        ->assertDontSee('Sensitive database connection details');
    $maintenance->assertSee('We’re making a little magic')
        ->assertSee('Try again')
        ->assertDontSee('Private maintenance details');
});
