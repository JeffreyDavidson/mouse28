import { parseArgs } from 'node:util';
import { writeFile } from 'node:fs/promises';
import { chromium } from 'playwright';
import { navigationTimings } from './navigation-timings.mjs';
import { benchmarkFailures } from './benchmark-failures.mjs';

const { values } = parseArgs({ options: {
    base: { type: 'string' },
    paths: { type: 'string' },
    profile: { type: 'string', default: 'slow-mobile' },
    runs: { type: 'string', default: '3' },
    output: { type: 'string' },
    revision: { type: 'string' },
} });
if (!values.base) throw new Error('Pass --base https://mouse28.test (or an explicitly chosen production/staging origin).');
const base = new URL(values.base);
if (!['http:', 'https:'].includes(base.protocol) || base.username || base.password) throw new Error('Use an HTTP(S) origin without credentials.');
const runs = Number(values.runs);
if (!Number.isInteger(runs) || runs < 1 || runs > 5) throw new Error('--runs must be between 1 and 5.');
if (!['desktop', 'slow-mobile'].includes(values.profile)) throw new Error('--profile must be desktop or slow-mobile.');
const mobile = values.profile === 'slow-mobile';
const browser = await chromium.launch();
const samples = [];
const report = {
    measuredAt: new Date().toISOString(), origin: base.origin, browser: browser.version(), revision: values.revision ?? null,
    profile: values.profile, runs, observationMs: 10000,
    conditions: mobile ? { viewport: '390x844', dpr: 2, cpuSlowdown: 4, latencyMs: 150, downloadBytesPerSecond: 200000, uploadBytesPerSecond: 93750 } : { viewport: '1440x1000', dpr: 1, throttling: 'none' },
    samples,
};

try {
    let paths = values.paths?.split(',') ?? ['/', '/blog', '/episodes', '/about', '/contact', '/search?q=disney', '/guides'];
    if (!values.paths) {
        const discovery = await browser.newPage();
        for (const [index, prefix] of [['/blog', '/blog/'], ['/episodes', '/episodes/'], ['/guides', '/guides/']]) {
            const response = await discovery.goto(new URL(index, base.origin).href, { waitUntil: 'domcontentloaded', timeout: 30000 });
            if (response?.status() !== 200) continue;
            const path = await discovery.locator('main a[href]').evaluateAll((links, prefix) => links.map(link => new URL(link.href)).find(url => url.origin === location.origin && url.pathname.startsWith(prefix))?.pathname, prefix);
            if (path) paths.push(path);
        }
        await discovery.close();
    }
    paths = [...new Set(paths)];
    for (const path of paths) {
        const url = new URL(path, base.origin);
        if (url.origin !== base.origin || !path.startsWith('/') || /^\/(admin|preview)(\/|$)/.test(url.pathname)) throw new Error('Benchmark only same-origin public paths.');
        for (let run = 1; run <= runs; run++) {
            const context = await browser.newContext({ viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 }, deviceScaleFactor: mobile ? 2 : 1, isMobile: mobile, hasTouch: mobile });
            try {
                const page = await context.newPage();
                const cdp = await context.newCDPSession(page);
                await cdp.send('Network.enable');
                if (mobile) {
                    await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
                    await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: 200000, uploadThroughput: 93750 });
                }
                await page.addInitScript(() => {
                    window.pageMetrics = { lcp: 0, cls: 0, longTaskBlockingMs: 0 };
                    let sessionStart = 0, lastShift = 0, sessionValue = 0;
                    new PerformanceObserver(list => list.getEntries().forEach(entry => {
                        window.pageMetrics.lcp = entry.startTime;
                        window.pageMetrics.lcpElement = entry.element?.tagName;
                    })).observe({ type: 'largest-contentful-paint', buffered: true });
                    new PerformanceObserver(list => list.getEntries().forEach(entry => {
                        if (entry.hadRecentInput) return;
                        if (entry.startTime - lastShift > 1000 || entry.startTime - sessionStart > 5000) {
                            sessionStart = entry.startTime;
                            sessionValue = 0;
                        }
                        lastShift = entry.startTime;
                        sessionValue += entry.value;
                        window.pageMetrics.cls = Math.max(window.pageMetrics.cls, sessionValue);
                    })).observe({ type: 'layout-shift', buffered: true });
                    new PerformanceObserver(list => list.getEntries().forEach(entry => {
                        window.pageMetrics.longTaskBlockingMs += Math.max(0, entry.duration - 50);
                    })).observe({ type: 'longtask', buffered: true });
                });
                // New context is cold; repeat navigation in the SAME context measures browser cache reuse.
                for (const cache of ['cold', 'warm']) {
                    const failures = [], errorOrigins = {}, assets = new Map();
                    let bytes = 0, requests = 0, pageErrors = 0;
                    const onFinished = event => { bytes += event.encodedDataLength; };
                    const onRequest = () => { requests++; };
                    const onError = () => { pageErrors++; };
                    const onConsole = message => {
                        if (message.type() !== 'error') return;
                        const source = message.location().url;
                        const origin = source ? new URL(source).origin : 'unknown';
                        errorOrigins[origin] = (errorOrigins[origin] ?? 0) + 1;
                    };
                    const onCdpResponse = ({ response, type }) => {
                        const resource = new URL(response.url);
                        if (resource.origin !== base.origin) return;
                        if (response.status >= 400) failures.push({ path: resource.pathname, status: response.status, type });
                        if (!['Image', 'Script', 'Stylesheet', 'Font'].includes(type)) return;
                        const headers = Object.fromEntries(Object.entries(response.headers).map(([key, value]) => [key.toLowerCase(), value]));
                        assets.set(resource.pathname, { path: resource.pathname, type, cacheControl: headers['cache-control'] ?? null, expires: headers.expires ?? null, lastModified: headers['last-modified'] ?? null, encoding: headers['content-encoding'] ?? null, cached: Boolean(response.fromDiskCache || response.fromPrefetchCache) });
                    };
                    cdp.on('Network.loadingFinished', onFinished);
                    cdp.on('Network.requestWillBeSent', onRequest);
                    cdp.on('Network.responseReceived', onCdpResponse);
                    page.on('pageerror', onError);
                    page.on('console', onConsole);
                    try {
                        const response = await page.goto(url.href, { waitUntil: 'domcontentloaded', timeout: 30000 });
                        // Fixed window: third-party challenges can prevent networkidle indefinitely.
                        await page.waitForTimeout(10000);
                        const metrics = await page.evaluate(() => {
                            const navigation = performance.getEntriesByType('navigation')[0];
                            return { ...window.pageMetrics, ttfb: navigation.responseStart, fcp: performance.getEntriesByName('first-contentful-paint')[0]?.startTime ?? null, load: navigation.loadEventEnd || null,
                                navigation: Object.fromEntries(['startTime', 'domainLookupStart', 'domainLookupEnd', 'connectStart', 'connectEnd', 'secureConnectionStart', 'requestStart', 'responseStart', 'responseEnd'].map(key => [key, navigation[key]])),
                                incompleteImages: [...document.images].filter(img => !img.complete && img.getBoundingClientRect().top < innerHeight).length,
                                largestResources: performance.getEntriesByType('resource').filter(entry => new URL(entry.name).origin === location.origin).sort((a, b) => b.transferSize - a.transferSize).slice(0, 5).map(entry => ({ path: new URL(entry.name).pathname, bytes: entry.transferSize, duration: Math.round(entry.duration) })) };
                        });
                        const sample = { path, run, cache, status: response?.status(), ...metrics, ...navigationTimings(metrics.navigation), bytes, requests, pageErrors, errorOrigins, failures, assets: [...assets.values()] };
                        sample.failureReasons = benchmarkFailures(sample);
                        samples.push(sample);
                        if (sample.failureReasons.length > 0) process.exitCode = 1;
                        console.log(JSON.stringify({ path, run, cache, status: sample.status, lcp: Math.round(metrics.lcp), cls: metrics.cls, ttfb: Math.round(metrics.ttfb), bytes, pageErrors, failureReasons: sample.failureReasons }));
                    } catch (error) {
                        samples.push({ path, run, cache, error: error.name, failureReasons: ['navigation-error'] });
                        process.exitCode = 1;
                        console.error(`${path} ${cache}: ${error.name}`);
                    } finally {
                        cdp.off('Network.loadingFinished', onFinished);
                        cdp.off('Network.requestWillBeSent', onRequest);
                        cdp.off('Network.responseReceived', onCdpResponse);
                        page.off('pageerror', onError);
                        page.off('console', onConsole);
                    }
                }
            } finally { await context.close(); }
        }
    }
} finally {
    await browser.close();
    report.summary = [];
    for (const path of [...new Set(samples.map(sample => sample.path))]) {
        for (const cache of ['cold', 'warm']) {
            const group = samples.filter(sample => sample.path === path && sample.cache === cache && !sample.error);
            const failedRuns = samples.filter(sample => sample.path === path && sample.cache === cache && sample.failureReasons.length > 0).length;
            const median = key => {
                const sorted = group.map(sample => sample[key]).filter(value => typeof value === 'number').sort((a, b) => a - b);
                const middle = Math.floor(sorted.length / 2);
                return sorted.length ? (sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2) : null;
            };
            report.summary.push({ path, cache, completedRuns: group.length, failedRuns, medianLcpMs: median('lcp'), medianTtfbMs: median('ttfb'), medianPreRequestMs: median('preRequestMs'), medianResponseWaitMs: median('responseWaitMs'), medianDocumentTransferMs: median('documentTransferMs'), medianBytes: median('bytes'), worstCls: group.length ? Math.max(...group.map(sample => sample.cls)) : null });
        }
    }
    if (values.output) await writeFile(values.output, JSON.stringify(report, null, 2) + '\n');
    console.table(report.summary);
}
