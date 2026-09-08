// Accept only timing values, never the navigation URL or response headers.
export function navigationTimings(navigation) {
    return {
        dnsMs: navigation.domainLookupEnd - navigation.domainLookupStart,
        connectionMs: navigation.connectEnd - navigation.connectStart,
        tlsMs: navigation.secureConnectionStart > 0 ? navigation.connectEnd - navigation.secureConnectionStart : 0,
        preRequestMs: navigation.requestStart - navigation.startTime,
        responseWaitMs: navigation.responseStart - navigation.requestStart,
        documentTransferMs: navigation.responseEnd - navigation.responseStart,
    };
}
