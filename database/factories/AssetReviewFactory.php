<?php

namespace Database\Factories;

use App\Models\AssetReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AssetReview> */
class AssetReviewFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'version' => 'v1', 'files' => []];
    }
}
