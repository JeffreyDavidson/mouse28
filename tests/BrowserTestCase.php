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

    /**
     * Returns the strongest contrast ratio between the focused element's outline or
     * box-shadow colors and its nearest opaque background.
     */
    protected function focusIndicatorContrastScript(string $selector): string
    {
        $encodedSelector = json_encode($selector, JSON_THROW_ON_ERROR);

        return <<<JS
            (() => {
                const canvas = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
                const toRgba = (color) => {
                    canvas.clearRect(0, 0, 1, 1);
                    canvas.fillStyle = color;
                    canvas.fillRect(0, 0, 1, 1);
                    const [r, g, b, a] = canvas.getImageData(0, 0, 1, 1).data;

                    return { r, g, b, a: a / 255 };
                };
                const composite = (color, base) => ['r', 'g', 'b'].reduce((mixed, channel) => ({ ...mixed, [channel]: color[channel] * color.a + base[channel] * (1 - color.a) }), {});
                const luminance = (color) => ['r', 'g', 'b']
                    .map((channel) => color[channel] / 255)
                    .map((value) => value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4)
                    .reduce((total, value, index) => total + value * [0.2126, 0.7152, 0.0722][index], 0);
                const contrast = (first, second) => {
                    const [light, dark] = [luminance(first), luminance(second)].sort((a, b) => b - a);

                    return (light + 0.05) / (dark + 0.05);
                };

                const element = document.querySelector({$encodedSelector});
                let background = { r: 255, g: 255, b: 255, a: 1 };

                for (let ancestor = element.parentElement; ancestor; ancestor = ancestor.parentElement) {
                    const color = toRgba(getComputedStyle(ancestor).backgroundColor);

                    if (color.a > 0.99) {
                        background = color;
                        break;
                    }
                }

                element.focus();
                const styles = getComputedStyle(element);
                const indicators = [...styles.boxShadow.matchAll(/(?:rgba?|oklch|oklab|color|hsla?)\([^()]*(?:\([^()]*\)[^()]*)*\)/g)].map((match) => match[0]);

                if (styles.outlineStyle !== 'none' && Number.parseFloat(styles.outlineWidth) > 0) {
                    indicators.push(styles.outlineColor);
                }

                return Math.max(0, ...indicators.map(toRgba).filter((color) => color.a > 0).map((color) => contrast(composite(color, background), background)));
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
