import assert from 'node:assert/strict';
import { test } from 'node:test';
import { discoverDetailPaths } from './benchmark-page-discovery.mjs';

const protectedOrigin = 'https://staging.mouse28.com';
const accessCredentials = { clientId: 'benchmark-client-id', clientSecret: 'benchmark-client-secret' };

test('discovers protected detail pages after configuring Cloudflare Access routing', async () => {
    const operations = [];
    const discovery = {
        route: async pattern => operations.push(`route:${pattern}`),
        goto: async url => {
            operations.push(`goto:${new URL(url).pathname}`);

            return { status: () => 200 };
        },
        locator: () => ({
            evaluateAll: async (callback, criteria) =>
                callback(
                    [
                        { href: `${protectedOrigin}${criteria.prefix}fixture` },
                        { href: `https://example.test${criteria.prefix}external-content` },
                    ],
                    criteria,
                ),
        }),
        close: async () => operations.push('close'),
    };
    const browser = { newPage: async () => discovery };

    const paths = await discoverDetailPaths(browser, protectedOrigin, accessCredentials);

    assert.deepEqual(paths, ['/blog/fixture', '/episodes/fixture', '/guides/fixture']);
    assert.equal(operations[0], `route:${protectedOrigin}/**`);
    assert.equal(operations.at(-1), 'close');
});
