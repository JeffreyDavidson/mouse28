<?php

namespace Database\Factories;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactInquiry>
 */
class ContactInquiryFactory extends Factory
{
    /**
     * A new inquiry whose emails have not been attempted yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'type' => fake()->randomElement(ContactType::cases()),
            'message' => fake()->sentence(),
            'status' => ContactInquiryStatus::New,
        ];
    }
}
