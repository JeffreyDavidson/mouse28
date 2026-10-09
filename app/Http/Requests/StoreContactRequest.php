<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\ContactMessageData;
use App\Enums\ContactType;
use App\Rules\PassesTurnstile;
use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

#[ErrorBag('contact')]
class StoreContactRequest extends FormRequest
{
    private const string TURNSTILE_FIELD = 'cf-turnstile-response';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Enum>> */
    public function rules(): array
    {
        if ($this->isHoneypotSubmission()) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ContactType::class)],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * Verify Turnstile only once every other field is valid, so a rejected or honeypot
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
                    [self::TURNSTILE_FIELD => [new PassesTurnstile($turnstileVerifier, $this, Config::string('services.turnstile.contact_action'))]],
                );

                $errors->merge($turnstile->errors());
            },
        ];
    }

    public function toData(): ContactMessageData
    {
        $validated = $this->safe();

        return new ContactMessageData(
            name: $validated->string('name')
                ->toString(),
            email: $validated->string('email')
                ->toString(),
            type: ContactType::from($validated->string('type')
                ->toString()),
            message: $validated->string('message')
                ->toString(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return route('contact.create');
    }

    /**
     * A failed Turnstile check goes back to the previous page with every field flashed, as the
     * exception handler does; other failures keep the form request's redirect to the contact page.
     */
    protected function failedValidation(ValidatorContract $validator): void
    {
        if ($validator->errors()
            ->has(self::TURNSTILE_FIELD)) {
            throw new ValidationException($validator)->errorBag($this->errorBag);
        }

        parent::failedValidation($validator);
    }

    /** Bots that fill the hidden website field get the normal success response, and nothing is sent. */
    protected function passedValidation(): void
    {
        if ($this->isHoneypotSubmission()) {
            throw new HttpResponseException(redirect()->route('contact.create')
                ->with('success', true));
        }
    }

    private function isHoneypotSubmission(): bool
    {
        return $this->filled('website');
    }
}
