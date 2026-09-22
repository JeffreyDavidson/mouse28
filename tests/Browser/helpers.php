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
