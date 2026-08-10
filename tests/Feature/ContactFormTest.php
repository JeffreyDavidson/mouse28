<?php

namespace Tests\Feature;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');
        config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    }

    public function test_contact_page_renders_turnstile_widget(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false)
            ->assertSee('class="cf-turnstile"', false)
            ->assertSee('data-sitekey="test-site-key"', false);
    }

    public function test_valid_contact_submission_requires_successful_turnstile_verification(): void
    {
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        $response = $this->from('/contact')->post('/contact', $this->validPayload());

        $response->assertRedirect('/contact')
            ->assertSessionHas('success', true)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'dale@example.com',
            'subject' => 'Need help with Mouse28',
        ]);

        Mail::assertSent(ContactFormSubmitted::class);
        Mail::assertSent(ContactFormConfirmation::class);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'test-secret-key'
                && $request['response'] === 'turnstile-token';
        });
    }

    public function test_contact_submission_rejects_failed_turnstile_verification_before_persistence_or_mail(): void
    {
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        $response = $this->from('/contact')->post('/contact', $this->validPayload());

        $response->assertRedirect('/contact')
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_contact_submission_rejects_missing_turnstile_secret_before_persistence_or_mail(): void
    {
        Mail::fake();
        config()->set('services.turnstile.secret_key', null);

        $response = $this->from('/contact')->post('/contact', $this->validPayload());

        $response->assertRedirect('/contact')
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_honeypot_still_silently_accepts_bot_submissions_without_persistence_or_mail(): void
    {
        Mail::fake();

        $response = $this->from('/contact')->post('/contact', array_merge($this->validPayload(), [
            'website_url' => 'https://spam.example',
        ]));

        $response->assertRedirect('/contact')
            ->assertSessionHas('success', true);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_contact_form_rate_limit_is_applied_without_throttling_the_contact_page(): void
    {
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/contact')->post('/contact', array_merge($this->validPayload(), [
                'email' => "dale{$i}@example.com",
            ]))->assertSessionHasNoErrors();
        }

        $this->from('/contact')->post('/contact', array_merge($this->validPayload(), [
            'email' => 'dale-rate-limit@example.com',
        ]))->assertRedirect('/contact')
            ->assertSessionHasErrors('contact_rate_limit');

        $this->get('/contact')->assertOk();
    }

    private function validPayload(): array
    {
        return [
            'name' => 'Dale Cooper',
            'email' => 'dale@example.com',
            'subject' => 'Need help with Mouse28',
            'message' => 'The contact form needs secure bot protection.',
            'cf-turnstile-response' => 'turnstile-token',
        ];
    }
}
