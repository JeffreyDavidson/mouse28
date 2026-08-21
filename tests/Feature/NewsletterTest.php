<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.resend.key', 'resend-test-key');
        config()->set('services.resend.audience_id', 'audience-test-id');
        config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
        config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
        config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
        config()->set('services.turnstile.newsletter_action', 'newsletter');
        config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);
    }

    public function test_newsletter_forms_render_bot_protection(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-action="newsletter"', false)
            ->assertSee('name="website_url"', false);
    }

    public function test_valid_newsletter_signup_is_sent_to_configured_resend_audience(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'newsletter',
                'hostname' => 'mouse28.com',
            ]),
            'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], 201),
        ]);

        $this->from(route('home'))
            ->post(route('newsletter.store'), $this->validPayload())
            ->assertRedirect(route('home').'#newsletter')
            ->assertSessionHas('newsletter_success', true)
            ->assertSessionHasNoErrors();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.resend.com/audiences/audience-test-id/contacts'
            && $request['email'] === 'dale@example.com');
    }

    public function test_newsletter_signup_rejects_invalid_turnstile_response(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'contact-form',
                'hostname' => 'mouse28.com',
            ]),
        ]);

        $this->from(route('home'))
            ->post(route('newsletter.store'), $this->validPayload())
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('cf-turnstile-response');

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'api.resend.com'));
    }

    public function test_newsletter_honeypot_silently_accepts_bot_without_external_requests(): void
    {
        Http::fake();

        $this->from(route('home'))
            ->post(route('newsletter.store'), array_merge($this->validPayload(), [
                'website_url' => 'https://spam.example',
            ]))
            ->assertRedirect(route('home').'#newsletter')
            ->assertSessionHas('newsletter_success', true);

        Http::assertNothingSent();
    }

    public function test_newsletter_requires_a_configured_audience(): void
    {
        config()->set('services.resend.audience_id');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'newsletter',
                'hostname' => 'mouse28.com',
            ]),
        ]);

        $this->from(route('home'))
            ->post(route('newsletter.store'), $this->validPayload())
            ->assertRedirect(route('home').'#newsletter')
            ->assertSessionHas('newsletter_error');

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'api.resend.com'));
    }

    public function test_newsletter_rate_limit_is_applied(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'newsletter',
                'hostname' => 'mouse28.com',
            ]),
            'https://api.resend.com/*' => Http::response([], 201),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('home'))
                ->post(route('newsletter.store'), array_merge($this->validPayload(), [
                    'email' => "dale{$attempt}@example.com",
                ]))
                ->assertSessionHasNoErrors();
        }

        $this->from(route('home'))
            ->post(route('newsletter.store'), $this->validPayload())
            ->assertSessionHasErrors('newsletter_rate_limit');
    }

    private function validPayload(): array
    {
        return [
            'email' => 'dale@example.com',
            'cf-turnstile-response' => 'turnstile-token',
        ];
    }
}
