<?php

namespace Tests;

use Pest\Browser\Playwright\Playwright;

abstract class BrowserTestCase extends TestCase
{
    protected function tabKey(): string
    {
        // Option-Tab includes links and buttons in macOS WebKit's keyboard navigation.
        return PHP_OS_FAMILY === 'Darwin' && Playwright::defaultBrowserType()->toPlaywrightName() === 'webkit'
            ? 'Alt+Tab'
            : 'Tab';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withVite();
    }

    protected function horizontalOverflowScript(): string
    {
        return <<<'JS'
            (() => document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0)()
            JS;
    }

    protected function undersizedControlsScript(): string
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
                        && (bounds.width < 48 || bounds.height < 48);
                }).map((control) => {
                    const bounds = control.getBoundingClientRect();
                    const identity = control.id ? `#${control.id}` : control.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                    return `${control.tagName.toLowerCase()}${identity} (${Math.round(bounds.width)}x${Math.round(bounds.height)})`;
                }).join('|');
            })()
            JS;
    }

    protected function missingFocusIndicatorsScript(): string
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
                    'audio[controls]',
                    '[tabindex]:not([tabindex="-1"])',
                ].join(','));

                return [...focusableElements].filter((element) => {
                    const bounds = element.getBoundingClientRect();

                    if (element.closest('[aria-hidden="true"]') || bounds.width === 0 || bounds.height === 0) {
                        return false;
                    }

                    element.blur();
                    const unfocusedShadow = window.getComputedStyle(element).boxShadow;
                    element.focus();

                    const styles = window.getComputedStyle(element);
                    const hasOutline = styles.outlineStyle !== 'none'
                        && styles.outlineColor !== 'rgba(0, 0, 0, 0)'
                        && Number.parseFloat(styles.outlineWidth) > 0;
                    const hasBoxShadow = styles.boxShadow !== 'none' && styles.boxShadow !== unfocusedShadow;

                    return document.activeElement !== element || (! hasOutline && ! hasBoxShadow);
                }).map((element) => {
                    const identity = element.id ? `#${element.id}` : element.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                    return `${element.tagName.toLowerCase()}${identity}`;
                }).join('|');
            })()
            JS;
    }
}
