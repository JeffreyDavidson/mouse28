<?php

namespace Database\Factories;

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Models\Guide;
use App\Models\User;
use Database\Factories\Concerns\HasSeoState;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Guide> */
class GuideFactory extends Factory
{
    use HasSeoState;

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
            'source_url' => fake()->url(),
            'last_reviewed_at' => now()->subWeek(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    /** Credits the guide to these authors, in byline order. */
    public function withAuthors(User ...$authors): static
    {
        return $this->afterCreating(function (Guide $record) use ($authors): void {
            $record->syncAuthors(array_map(fn (User $author): int => $author->id, $authors));
        });
    }

    /** Credits the guide to the first author, creating an author when none exists. */
    public function credited(): static
    {
        return $this->withAuthors(User::authors()->first() ?? User::factory()->author()->create());
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
