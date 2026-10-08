<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\PassesTurnstile;
use App\Services\TurnstileVerifier;
use App\Support\SafeReturnUrl;
use Closure;
use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Validator;

#[ErrorBag('newsletter')]
class SubscribeNewsletterRequest extends FormRequest
{
    public const string CHECK_YOUR_EMAIL = 'Check your email to confirm your sign-up.';

    private const string TURNSTILE_FIELD = 'cf-turnstile-response';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        if ($this->isHoneypotSubmission()) {
            return [];
        }

        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * Verify Turnstile only once the email is valid, so a rejected or honeypot
     * submission never makes the remote verification call.
     *
     * @return list<Closure(Validator): void>
     */
    public function after(TurnstileVerifier $turnstileVerifier): array
    {
        return [
            function (Validator $validator) use ($turnstileVerifier): void {
                $errors = $validator->errors();

                if ($this->isHoneypotSubmission() || $errors->isNotEmpty()) {
                    return;
                }

                $turnstile = validator(
                    [self::TURNSTILE_FIELD => $this->input(self::TURNSTILE_FIELD)],
                    [self::TURNSTILE_FIELD => [new PassesTurnstile($turnstileVerifier, $this, Config::string('services.turnstile.newsletter_action'))]],
                );

                $errors->merge($turnstile->errors());
            },
        ];
    }

    public function redirectUrl(): string
    {
        return SafeReturnUrl::from($this, route('home')).'#newsletter';
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirectUrl();
    }

    /** Bots that fill the hidden website_url field get the normal answer, and nothing is stored or sent. */
    protected function passedValidation(): void
    {
        if ($this->isHoneypotSubmission()) {
            throw new HttpResponseException(redirect($this->redirectUrl())->with('newsletter_success', self::CHECK_YOUR_EMAIL));
        }
    }

    private function isHoneypotSubmission(): bool
    {
        return $this->filled('website_url');
    }
}
