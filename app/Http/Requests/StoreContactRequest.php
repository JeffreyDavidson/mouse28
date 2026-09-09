<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\FormRequest;

#[ErrorBag('contact')]
class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        if ($this->filled('website_url')) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /** @return array{name: string, email: string, subject: string, message: string} */
    public function messageAttributes(): array
    {
        $validated = $this->safe();

        return [
            'name' => $validated->string('name')->toString(),
            'email' => $validated->string('email')->toString(),
            'subject' => $validated->string('subject')->toString(),
            'message' => $validated->string('message')->toString(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('contact.show');
    }
}
