<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Episode> */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'description' => fake()->sentence(18),
            'show_notes' => fake()->paragraphs(3, true),
            'transcript' => fake()->paragraphs(4, true),
            'episode_number' => fake()->unique()->numberBetween(1, 10000),
            'season_number' => 1,
            'duration_seconds' => 1800,
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
