<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Factories\Concerns\HasSeoState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    use HasSeoState;

    #[\Override]
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(6),
            'excerpt' => fake()->sentence(18),
            'content' => fake()->paragraphs(5, true),
            'category_id' => Category::factory(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    /** Files the post under the category with this slug, creating it when it is missing. */
    public function inCategory(string $slug): static
    {
        return $this->state(fn (): array => [
            'category_id' => Category::query()->firstOrCreate(['slug' => $slug], ['name' => Str::headline($slug)])->id,
        ]);
    }

    /** Credits the post to these authors, in byline order. */
    public function withAuthors(User ...$authors): static
    {
        return $this->afterCreating(function (Post $record) use ($authors): void {
            $record->syncAuthors(array_map(fn (User $author): int => $author->id, $authors));
        });
    }

    /** Credits the post to the first author, creating an author when none exists. */
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
