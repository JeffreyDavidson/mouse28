<?php

namespace Database\Factories;

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
        $title = fake()->unique()->sentence(5);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->sentence(16),
            'content' => fake()->paragraphs(3, true),
            'is_published' => true,
            'published_at' => Date::now()->subDay(),
            'sent_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'is_published' => true,
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
