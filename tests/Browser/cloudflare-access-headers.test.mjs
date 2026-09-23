import assert from 'node:assert/strict';
import { test } from 'node:test';
import { cloudflareAccessHeadersForRequest } from './cloudflare-access-headers.mjs';

const credentials = { clientId: 'benchmark-client-id', clientSecret: 'benchmark-client-secret' };
const protectedOrigin = 'https://staging.mouse28.com';

test('adds Cloudflare Access headers only to the protected staging origin', () => {
    assert.deepEqual(
        cloudflareAccessHeadersForRequest(`${protectedOrigin}/blog`, protectedOrigin, credentials),
        {
            'cf-access-client-id': credentials.clientId,
            'cf-access-client-secret': credentials.clientSecret,
        },
    );
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
