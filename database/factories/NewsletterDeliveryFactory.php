<?php

namespace Database\Factories;

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<NewsletterDelivery>
 */
class NewsletterDeliveryFactory extends Factory
{
    /**
     * A queued delivery that has not been sent yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'newsletter_issue_id' => NewsletterIssue::factory(),
            'subscriber_id' => Subscriber::factory(),
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'sent_at' => Date::now(),
        ]);
    }
}
