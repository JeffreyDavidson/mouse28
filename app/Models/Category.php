<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description')]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('editorial')
            ->logOnly(['name', 'slug', 'description'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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
