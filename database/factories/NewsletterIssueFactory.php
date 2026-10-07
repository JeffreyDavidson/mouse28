<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<NewsletterIssue>
 */
class NewsletterIssueFactory extends Factory
{
    /**
     * A live issue that has not been emailed yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(5),
            'excerpt' => fake()->sentence(16),
            'content' => fake()->paragraphs(3, true),
            'status' => PublishStatus::Published,
            'published_at' => Date::now()->subDay(),
            'sent_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Scheduled,
            'published_at' => Date::now()->addDay(),
        ]);
    }

    /** A live issue that has already been emailed. */
    public function sent(): static
    {
        return $this->state(fn (): array => [
            'sent_at' => Date::now()->subHour(),
        ]);
    }
}
