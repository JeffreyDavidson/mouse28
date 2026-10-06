<?php

use App\Http\Controllers\ResendWebhookController;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\call;

covers(ResendWebhookController::class);

pest()->use(RefreshDatabase::class);

const WEBHOOK_SECRET = 'whsec_'.'dGVzdC1zZWNyZXQtdmFsdWU=';

beforeEach(function (): void {
    config()->set('services.resend.webhook_secret', WEBHOOK_SECRET);
});

/** @return array<string, string> */
function signedWebhookHeaders(string $body, ?int $timestamp = null, string $secret = WEBHOOK_SECRET): array
{
    $timestamp ??= time();
    $key = base64_decode(substr($secret, 6));
    $signature = base64_encode(hash_hmac('sha256', "msg_1.{$timestamp}.{$body}", $key, true));

    return [
        'HTTP_SVIX_ID' => 'msg_1',
        'HTTP_SVIX_TIMESTAMP' => (string) $timestamp,
        'HTTP_SVIX_SIGNATURE' => "v1,{$signature}",
        'CONTENT_TYPE' => 'application/json',
    ];
}

/**
 * @param  array<string, mixed>  $event
 * @return TestResponse<Response>
 */
function postWebhook(array $event): TestResponse
{
    $body = json_encode($event, JSON_THROW_ON_ERROR);

    return call('POST', route('webhooks.resend'), [], [], [], signedWebhookHeaders($body), $body);
}

/** @return array<string, mixed> */
function complaintEvent(string $email): array
{
    return ['type' => 'email.complained', 'data' => ['to' => [$email]]];
}

test('a verified complaint suppresses the subscriber', function (): void {
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertNoContent();

    expect($reader->refresh()->isSuppressed())->toBeTrue();
});

test('a verified event we do not use is accepted and ignored', function (): void {
    $reader = Subscriber::factory()->create();

    postWebhook(['type' => 'email.delivered', 'data' => ['to' => [$reader->email]]])->assertNoContent();

    expect($reader->refresh()->isActive())->toBeTrue();
});

test('a repeated delivery is harmless', function (): void {
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertNoContent();
    postWebhook(complaintEvent($reader->email))->assertNoContent();

    expect(Subscriber::query()->whereNotNull('suppressed_at')->count())->toBe(1);
});

test('requests with a bad signature are rejected and change nothing', function (string $case): void {
    $reader = Subscriber::factory()->create();
    $body = json_encode(complaintEvent($reader->email), JSON_THROW_ON_ERROR);
    $server = [
        'missing headers' => ['CONTENT_TYPE' => 'application/json'],
        'old timestamp' => signedWebhookHeaders($body, time() - 3600),
        'wrong secret' => signedWebhookHeaders($body, null, 'whsec_'.base64_encode('another-secret')),
        'tampered body' => signedWebhookHeaders('{"type":"email.complained","data":{"to":["x@example.com"]}}'),
    ][$case];

    call('POST', route('webhooks.resend'), [], [], [], $server, $body)->assertForbidden();

    expect($reader->refresh()->isActive())->toBeTrue();
})->with(['missing headers', 'old timestamp', 'wrong secret', 'tampered body']);

test('a header carrying an old signature beside the valid one is accepted', function (): void {
    // Arrange
    $reader = Subscriber::factory()->create();
    $body = json_encode(complaintEvent($reader->email), JSON_THROW_ON_ERROR);
    $headers = signedWebhookHeaders($body);
    $headers['HTTP_SVIX_SIGNATURE'] = 'v1,b2xkLXNpZ25hdHVyZQ== '.$headers['HTTP_SVIX_SIGNATURE'];

    // Act
    $response = call('POST', route('webhooks.resend'), [], [], [], $headers, $body);

    // Assert
    $response->assertNoContent();
    expect($reader->refresh()->isSuppressed())->toBeTrue();
});

test('a malformed signature header is rejected with forbidden instead of an error', function (string $signature): void {
    // Arrange
    $reader = Subscriber::factory()->create();
    $body = json_encode(complaintEvent($reader->email), JSON_THROW_ON_ERROR);
    $server = [...signedWebhookHeaders($body), 'HTTP_SVIX_SIGNATURE' => $signature];

    // Act
    $response = call('POST', route('webhooks.resend'), [], [], [], $server, $body);

    // Assert
    $response->assertForbidden();
    expect($reader->refresh()->isActive())->toBeTrue();
})->with([
    'a version with no signature' => ['v1'],
    'an empty signature' => ['v1,'],
    'a signature with no version' => [',abc'],
    'a valid pair followed by a bare version' => ['v1,abc v1'],
    'a signature with extra spaces' => ['v1,abc  v1,def'],
    'only spaces' => ['   '],
]);

test('an unconfigured secret answers service unavailable', function (?string $secret): void {
    config()->set('services.resend.webhook_secret', $secret);
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertServiceUnavailable();

    expect($reader->refresh()->isActive())->toBeTrue();
})->with([null, '']);

test('a signed body that is not JSON is rejected', function (): void {
    $body = 'not json';

    call('POST', route('webhooks.resend'), [], [], [], signedWebhookHeaders($body), $body)->assertUnprocessable();
});

test('the endpoint uses no session or forgery middleware', function (): void {
    $route = Route::getRoutes()->getByName('webhooks.resend');

    expect($route?->excludedMiddleware())->toContain('web')
        ->and($route?->gatherMiddleware())->toContain('throttle:resend-webhook');
});

test('the endpoint is rate limited per address', function (): void {
    config()->set('mouse28.rate_limits.resend_webhook_per_minute', 1);
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertNoContent();
    postWebhook(complaintEvent($reader->email))->assertTooManyRequests();
});
