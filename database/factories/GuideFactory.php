<?php

namespace Database\Factories;

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Models\Guide;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Guide> */
class GuideFactory extends Factory
{
    #[\Override]
    protected $model = Guide::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->sentence(18),
            'content' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(GuideCategory::cases()),
            'author' => fake()->randomElement(ContentAuthor::cases()),
            'source_url' => fake()->url(),
            'last_reviewed_at' => now()->subWeek(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
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
            'published_at' => now()->addDay(),
        ]);
    }
}
