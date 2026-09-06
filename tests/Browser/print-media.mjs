import assert from 'node:assert/strict';
import { realpath } from 'node:fs/promises';
import { join, sep } from 'node:path';
import { chromium, firefox, webkit } from 'playwright';

let input = '';
for await (const chunk of process.stdin) input += chunk;
const { browser: engine, pages, publicPath } = JSON.parse(input);
const browser = await ({ chromium, firefox, webkit })[engine].launch();
const root = await realpath(publicPath);

try {
    for (const html of pages) {
        const page = await browser.newPage();
        page.setDefaultTimeout(10000);
        // Replay Laravel's actual HTML and built assets without a second application server.
        await page.route('**/*', async (route) => {
            if (route.request().isNavigationRequest() && route.request().frame() === page.mainFrame()) {
                return route.fulfill({ contentType: 'text/html', body: html });
            }
            const pathname = new URL(route.request().url()).pathname;
            if (!pathname.startsWith('/build/')) return route.abort();
            const path = await realpath(join(root, pathname));
            assert.ok(path.startsWith(root + sep));
            return route.fulfill({ path });
        });
        await page.goto('http://localhost/');
        assert.equal(await page.locator('body > header').isVisible(), true);
        await page.emulateMedia({ media: 'print' });
        assert.equal(await page.evaluate(() => matchMedia('print').matches), true);
        await page.locator('body > header').waitFor({ state: 'hidden' });
        assert.equal(await page.locator('body > header').isVisible(), false);
        assert.equal(await page.locator('footer').isVisible(), false);
        assert.equal(await page.locator('main h1').isVisible(), true);
        assert.equal(await page.locator('.blog-article-content').isVisible(), true);
        assert.equal(await page.locator('.blog-article-content').evaluate(el => getComputedStyle(el).maxWidth), 'none');
        assert.equal(await page.locator('[data-print-hidden]').evaluateAll(elements => elements.every(el => getComputedStyle(el).display === 'none')), true);
        const link = page.locator('.blog-article-content a').first();
        // Firefox serializes attr(href) instead of resolving it in computed style.
        assert.equal(await link.getAttribute('href'), 'https://example.com/accessible-planning');
        assert.match(await link.evaluate(el => getComputedStyle(el, '::after').content), /https:\/\/example\.com\/accessible-planning|attr\(href\)/);
        await page.emulateMedia({ media: 'screen' });
        await page.locator('body > header').waitFor({ state: 'visible' });
        assert.equal(await page.locator('body > header').isVisible(), true);
        await page.close();
    }
} finally {
    await browser.close();
}
