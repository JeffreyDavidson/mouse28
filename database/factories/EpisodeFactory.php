<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Episode;
use Database\Factories\Concerns\HasSeoState;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Episode> */
class EpisodeFactory extends Factory
{
    use HasSeoState;

    #[\Override]
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
