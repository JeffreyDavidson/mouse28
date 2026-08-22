<?php

namespace App\Http\Requests;

use App\Support\SafeReturnUrl;
use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\FormRequest;

#[ErrorBag('newsletter')]
class StoreNewsletterRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return SafeReturnUrl::from($this, route('home')).'#newsletter';
    }
}
