export function cloudflareAccessHeadersForRequest(requestUrl, protectedOrigin, credentials) {
    if (!credentials.clientId || !credentials.clientSecret) return {};

    if (new URL(requestUrl).origin !== protectedOrigin) return {};

    return {
        'cf-access-client-id': credentials.clientId,
        'cf-access-client-secret': credentials.clientSecret,
    };
}
