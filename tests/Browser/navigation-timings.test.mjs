import assert from 'node:assert/strict';
import { test } from 'node:test';
import { navigationTimings } from './navigation-timings.mjs';

const navigation = {
    startTime: 0, domainLookupStart: 5, domainLookupEnd: 10,
    connectStart: 10, secureConnectionStart: 20, connectEnd: 40,
    requestStart: 45, responseStart: 145, responseEnd: 165,
};

test('separates connection setup, response waiting, and document transfer', () => {
    const timings = navigationTimings(navigation);
    assert.deepEqual(timings, {
        dnsMs: 5, connectionMs: 30, tlsMs: 20,
        preRequestMs: 45, responseWaitMs: 100, documentTransferMs: 20,
    });
    assert.equal(timings.preRequestMs + timings.responseWaitMs, navigation.responseStart);
});

test('does not report TLS for an unsecured connection', () => {
    assert.equal(navigationTimings({ ...navigation, secureConnectionStart: 0 }).tlsMs, 0);
});

test('preserves zero-duration connection phases for reused connections', () => {
    const timings = navigationTimings({
        ...navigation, domainLookupStart: 5, domainLookupEnd: 5,
        connectStart: 5, connectEnd: 5, secureConnectionStart: 5,
    });
    assert.equal(timings.dnsMs, 0);
    assert.equal(timings.connectionMs, 0);
    assert.equal(timings.tlsMs, 0);
});

test('does not copy navigation URLs or other metadata into reports', () => {
    assert.deepEqual(navigationTimings({ ...navigation, name: 'https://example.test/?private=value' }), navigationTimings(navigation));
});
