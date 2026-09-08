export function benchmarkFailures(sample) {
    const reasons = [];
    if (sample.error) reasons.push('navigation-error');
    if (sample.pageErrors > 0) reasons.push('javascript-error');
    if (!sample.error && sample.status !== 200 && !(sample.path === '/guides' && sample.status === 404)) {
        reasons.push('document-status');
    }
    if (sample.failures?.some(failure => failure.status >= 400 && ['Image', 'Script', 'Stylesheet', 'Font'].includes(failure.type))) {
        reasons.push('asset-http-error');
    }
    return reasons;
}
