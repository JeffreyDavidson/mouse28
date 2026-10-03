<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Credits a post or guide to one or more authors (users) through its `{model}_user`
 * pivot, in byline order (`position`). The Laravel Architect keeps a single
 * `posts.user_id`; this many-to-many shape is mouse28-only.
 *
 * Eager-load `authors` wherever the byline renders.
 */
trait HasAuthors
{
    /** @return BelongsToMany<User, $this> */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Replaces the credited authors, keeping the given order as the byline order.
     *
     * @param  iterable<int|string>  $userIds
     */
    public function syncAuthors(iterable $userIds): void
    {
        $positions = [];

        foreach (array_values(array_unique([...$userIds])) as $position => $userId) {
            $positions[$userId] = ['position' => $position];
        }

        $this->authors()->sync($positions);
    }

    /**
     * One author shows their full name; several show their first names joined with
     * " & " in byline order; none falls back to the site team.
     *
     * @return Attribute<string, never>
     */
    protected function authorName(): Attribute
    {
        return Attribute::make(get: function (): string {
            $names = $this->authors->map(fn (User $author): string => $author->name);

            return match ($names->count()) {
                0 => 'Mouse28 Team',
                1 => $names->sole(),
                default => $names->map(fn (string $name): string => Str::before($name, ' '))->implode(' & '),
            };
        });
    }

    /** @return Attribute<string, never> */
    protected function authorInitials(): Attribute
    {
        return Attribute::make(get: fn (): string => Str::initials($this->author_name, capitalize: true));
    }
}
