import { routeCloudflareAccessRequests } from './cloudflare-access-headers.mjs';

export async function discoverDetailPaths(browser, baseOrigin, accessCredentials) {
    const discovery = await browser.newPage();
    const paths = [];

    try {
        await routeCloudflareAccessRequests(discovery, baseOrigin, accessCredentials);

        for (const [index, prefix] of [
            ['/blog', '/blog/'],
            ['/episodes', '/episodes/'],
            ['/guides', '/guides/'],
        ]) {
            const response = await discovery.goto(new URL(index, baseOrigin).href, {
                waitUntil: 'domcontentloaded',
                timeout: 30000,
            });
            if (response?.status() !== 200) continue;

            const path = await discovery
                .locator('main a[href]')
                .evaluateAll(
                    (links, criteria) =>
                        links
                            .map(link => new URL(link.href))
                            .find(url => url.origin === criteria.origin && url.pathname.startsWith(criteria.prefix))
                            ?.pathname,
                    { origin: baseOrigin, prefix },
                );

            if (path) paths.push(path);
        }

        return paths;
    } finally {
        await discovery.close();
    }
}
