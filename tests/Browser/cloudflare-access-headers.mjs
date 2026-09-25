export function cloudflareAccessHeadersForRequest(requestUrl, protectedOrigin, credentials) {
    if (!credentials.clientId || !credentials.clientSecret) return {};

    if (new URL(requestUrl).origin !== protectedOrigin) return {};

    return {
        'cf-access-client-id': credentials.clientId,
        'cf-access-client-secret': credentials.clientSecret,
    };
}

export async function routeCloudflareAccessRequests(target, protectedOrigin, credentials) {
    if (!credentials.clientId || !credentials.clientSecret) return;

    await target.route(`${protectedOrigin}/**`, async route => {
        const headers = cloudflareAccessHeadersForRequest(route.request().url(), protectedOrigin, credentials);

        await route.continue({
            headers: { ...route.request().headers(), ...headers },
        });
    });
}
