import assert from 'node:assert/strict';
import { test } from 'node:test';
import { benchmarkFailures } from './benchmark-failures.mjs';

const healthy = { path: '/', status: 200, pageErrors: 0, failures: [] };

test('returns no failures for a healthy page', () => {
    assert.deepEqual(benchmarkFailures(healthy), []);
});

for (const type of ['Image', 'Script', 'Stylesheet', 'Font']) {
        test(`reports ${type.toLowerCase()} asset HTTP errors`, () => {
        assert.deepEqual(benchmarkFailures({ ...healthy, failures: [{ type, status: 404 }] }), ['asset-http-error']);
    });
}

test('allows the dormant guides document while reporting its broken assets', () => {
    const sample = { ...healthy, path: '/guides', status: 404, failures: [{ type: 'Document', status: 404 }] };
    assert.deepEqual(benchmarkFailures(sample), []);
    sample.failures.push({ type: 'Stylesheet', status: 500 });
    assert.deepEqual(benchmarkFailures(sample), ['asset-http-error']);
});

test('reports unexpected document responses', () => {
    assert.deepEqual(benchmarkFailures({ ...healthy, status: 404 }), ['document-status']);
    assert.deepEqual(benchmarkFailures({ ...healthy, path: '/guides', status: 500 }), ['document-status']);
});

test('reports navigation and JavaScript errors separately', () => {
    assert.deepEqual(benchmarkFailures({ path: '/', error: 'TimeoutError' }), ['navigation-error']);
    assert.deepEqual(benchmarkFailures({ ...healthy, pageErrors: 1 }), ['javascript-error']);
});

test('ignores console-only third-party errors', () => {
    assert.deepEqual(benchmarkFailures({ ...healthy, errorOrigins: { 'https://example.test': 1 } }), []);
});
