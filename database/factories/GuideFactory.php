<?php

namespace Database\Factories;

use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Guide> */
class GuideFactory extends Factory
{
    protected $model = Guide::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->sentence(18),
            'body' => fake()->paragraphs(5, true),
            'category' => fake()->randomKey(Guide::CATEGORIES),
            'author' => fake()->randomKey(Post::AUTHORS),
            'source_url' => fake()->url(),
            'last_reviewed_at' => now()->subWeek(),
            'is_published' => true,
            'published_at' => now()->subDay(),
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
            'published_at' => now()->addDay(),
        ]);
    }
}
