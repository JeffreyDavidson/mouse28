<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialProfile>
 */
class SocialProfileFactory extends Factory
{
    /**
     * An enabled profile shown in the footer only.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => SocialPlatform::Instagram,
            'label' => null,
            'url' => 'https://example.com/'.fake()->unique()
                ->slug(2),
            'is_enabled' => true,
            'show_in_footer' => true,
            'show_on_contact' => false,
            'sort_order' => 10,
        ];
    }

    /** A profile shown on the contact page as well as the footer. */
    public function onContactPage(): static
    {
        return $this->state(fn (): array => ['show_on_contact' => true]);
    }
}
