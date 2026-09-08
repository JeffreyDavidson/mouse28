import assert from 'node:assert/strict';
import { test } from 'node:test';
import { benchmarkFailures } from './benchmark-failures.mjs';

const healthy = { path: '/', status: 200, pageErrors: 0, failures: [] };

test('healthy pages pass', () => {
    assert.deepEqual(benchmarkFailures(healthy), []);
});

for (const type of ['Image', 'Script', 'Stylesheet', 'Font']) {
    test(`${type} HTTP errors fail an otherwise healthy page`, () => {
        assert.deepEqual(benchmarkFailures({ ...healthy, failures: [{ type, status: 404 }] }), ['asset-http-error']);
    });
}

test('the dormant guides document is allowed but broken assets are not', () => {
    const sample = { ...healthy, path: '/guides', status: 404, failures: [{ type: 'Document', status: 404 }] };
    assert.deepEqual(benchmarkFailures(sample), []);
    sample.failures.push({ type: 'Stylesheet', status: 500 });
    assert.deepEqual(benchmarkFailures(sample), ['asset-http-error']);
});

test('unexpected document responses still fail', () => {
    assert.deepEqual(benchmarkFailures({ ...healthy, status: 404 }), ['document-status']);
    assert.deepEqual(benchmarkFailures({ ...healthy, path: '/guides', status: 500 }), ['document-status']);
});

test('navigation and JavaScript errors remain failures', () => {
    assert.deepEqual(benchmarkFailures({ path: '/', error: 'TimeoutError' }), ['navigation-error']);
    assert.deepEqual(benchmarkFailures({ ...healthy, pageErrors: 1 }), ['javascript-error']);
});

test('console-only third-party errors remain diagnostic', () => {
    assert.deepEqual(benchmarkFailures({ ...healthy, errorOrigins: { 'https://example.test': 1 } }), []);
});
