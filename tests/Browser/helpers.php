<?php

/** @param list<string> $glyphs */
function browserDecorativeGlyphCountScript(array $glyphs): string
{
    $encodedGlyphs = json_encode($glyphs, JSON_THROW_ON_ERROR);

    return <<<JS
        (() => {
            const glyphs = {$encodedGlyphs};
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let count = 0;

            while (walker.nextNode()) {
                const parent = walker.currentNode.parentElement;

                if (! parent?.closest('[aria-hidden="true"]') && glyphs.some((glyph) => walker.currentNode.textContent.includes(glyph))) {
                    count++;
                }
            }

            return count;
        })()
        JS;
}

/**
 * Build a script that resolves true once the condition holds, or false after the timeout.
 *
 * Pest's assertScript() evaluates once, so interactions that finish asynchronously
 * must be awaited in the browser instead of sampled immediately.
 */
function browserWaitForScript(string $condition, int $timeoutMilliseconds = 10000): string
{
    return <<<JS
        function() {
            return new Promise((resolve) => {
                const deadline = performance.now() + {$timeoutMilliseconds};
                const check = () => {
                    if ({$condition}) {
                        resolve(true);
                    } else if (performance.now() > deadline) {
                        resolve(false);
                    } else {
                        setTimeout(check, 25);
                    }
                };

                check();
            });
        }
        JS;
}

/**
 * Build a script that resolves true once the Livewire component at the selector has booted.
 *
 * Livewire loads asynchronously, so interactions before it boots fall back to plain links
 * or inert buttons.
 */
function browserLivewireReadyScript(string $selector): string
{
    return browserWaitForScript("document.querySelector('{$selector}')?.__livewire !== undefined");
}

/**
 * Build a script that resolves true once Alpine has initialised the component at the selector.
 *
 * Alpine starts after the page loads, so interactions before it boots reach an inert element.
 */
function browserAlpineReadyScript(string $selector): string
{
    return browserWaitForScript("document.querySelector('{$selector}')?._x_dataStack !== undefined");
}
