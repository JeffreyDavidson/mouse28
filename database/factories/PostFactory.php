<?php

namespace Database\Factories;

use App\Enums\ContentAuthor;
use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    #[\Override]
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->sentence(18),
            'content' => fake()->paragraphs(5, true),
            'category_id' => Category::factory(),
            'author' => fake()->randomElement(ContentAuthor::cases()),
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
