<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method static Builder<static> authors()
 */
#[Fillable([
    'name',
    'email',
    'password',
    'bio',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery, Notifiable;

    /**
     * New users start without admin access or author credit, matching the column defaults, so neither flag is ever null.
     *
     * @var array<string, mixed>
     */
    #[\Override]
    protected $attributes = [
        'is_admin' => false,
        'is_author' => false,
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Users who can be credited as post and guide authors, in creation order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function authors(Builder $query): void
    {
        $query->where('is_author', true)
            ->orderBy('id');
    }

    /**
     * Every author's id, in creation order (Jeffrey, then Cassie, for the migrated authors).
     *
     * @return list<int>
     */
    public static function authorIds(): array
    {
        return array_values(static::query()
            ->authors()
            ->get()
            ->map(fn (User $author): int => $author->id)
            ->all());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_author' => 'boolean',
        ];
    }
}
