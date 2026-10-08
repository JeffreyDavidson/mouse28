<?php

namespace App\Models;

use App\Models\Concerns\LogsEditorialActivity;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

#[Fillable('name', 'slug', 'description')]
#[Sluggable(from: 'name', maxLength: 255)]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, LogsEditorialActivity;

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<Post, $this> */
    public function publishedPosts(): HasMany
    {
        return $this->posts()
            ->published();
    }
}
