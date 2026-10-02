<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\ContactMessageData;
use App\Enums\ContactType;
use Illuminate\Foundation\Http\Attributes\ErrorBag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

#[ErrorBag('contact')]
class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Enum>> */
    public function rules(): array
    {
        if ($this->filled('website')) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ContactType::class)],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function toData(): ContactMessageData
    {
        $validated = $this->safe();

        return new ContactMessageData(
            name: $validated->string('name')->toString(),
            email: $validated->string('email')->toString(),
            type: ContactType::from($validated->string('type')->toString()),
            message: $validated->string('message')->toString(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return route('contact.create');
    }
}
