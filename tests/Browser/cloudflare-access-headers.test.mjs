import assert from 'node:assert/strict';
import { test } from 'node:test';
import { cloudflareAccessHeadersForRequest, routeCloudflareAccessRequests } from './cloudflare-access-headers.mjs';

const credentials = { clientId: 'benchmark-client-id', clientSecret: 'benchmark-client-secret' };
const protectedOrigin = 'https://staging.mouse28.com';

test('adds Cloudflare Access headers only to the protected staging origin', () => {
    assert.deepEqual(cloudflareAccessHeadersForRequest(`${protectedOrigin}/blog`, protectedOrigin, credentials), {
        'cf-access-client-id': credentials.clientId,
        'cf-access-client-secret': credentials.clientSecret,
    });
});

test('does not send Cloudflare Access headers to a different origin', () => {
    assert.deepEqual(
        cloudflareAccessHeadersForRequest('https://example.test/image.webp', protectedOrigin, credentials),
        {},
    );
});

test('does not send Cloudflare Access headers when credentials are unavailable', () => {
    assert.deepEqual(
        cloudflareAccessHeadersForRequest(`${protectedOrigin}/`, protectedOrigin, { clientId: '', clientSecret: '' }),
        {},
    );
});

test('routes protected requests through Cloudflare Access while preserving browser headers', async () => {
    let routeHandler;
    let routePattern;
    let continuedOptions;
    const browserHeaders = { accept: 'text/html', 'accept-language': 'en-US' };
    const target = {
        route: async (pattern, handler) => {
            routePattern = pattern;
            routeHandler = handler;
        },
    };

    await routeCloudflareAccessRequests(target, protectedOrigin, credentials);
    await routeHandler({
        request: () => ({
            url: () => `${protectedOrigin}/episodes/example`,
            headers: () => browserHeaders,
        }),
        continue: async options => {
            continuedOptions = options;
        },
    });

    assert.equal(routePattern, `${protectedOrigin}/**`);
    assert.deepEqual(continuedOptions.headers, {
        ...browserHeaders,
        'cf-access-client-id': credentials.clientId,
        'cf-access-client-secret': credentials.clientSecret,
    });
});

test('does not register a Cloudflare Access route without both credentials', async () => {
    let routeWasRegistered = false;
    const target = {
        route: async () => {
            routeWasRegistered = true;
        },
    };

    await routeCloudflareAccessRequests(target, protectedOrigin, { clientId: credentials.clientId, clientSecret: '' });

    assert.equal(routeWasRegistered, false);
});
