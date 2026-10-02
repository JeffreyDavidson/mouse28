<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialPlatform;
use Database\Factories\SocialProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property SocialPlatform $platform
 * @property string|null $label
 * @property string $url
 * @property bool $is_enabled
 * @property bool $show_in_footer
 * @property bool $show_on_contact
 * @property int $sort_order
 *
 * @method static Builder<static> forFooter()
 * @method static Builder<static> forContactPage()
 */
#[Fillable('platform', 'label', 'url', 'is_enabled', 'show_in_footer', 'show_on_contact', 'sort_order')]
class SocialProfile extends Model
{
    /** @use HasFactory<SocialProfileFactory> */
    use HasFactory;

    /** @param Builder<SocialProfile> $query */
    #[Scope]
    protected function forFooter(Builder $query): void
    {
        $query->enabledInOrder()->where('show_in_footer', true);
    }

    /** @param Builder<SocialProfile> $query */
    #[Scope]
    protected function forContactPage(Builder $query): void
    {
        $query->enabledInOrder()->where('show_on_contact', true);
    }

    /** @param Builder<SocialProfile> $query */
    #[Scope]
    protected function enabledInOrder(Builder $query): void
    {
        $query
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'is_enabled' => 'boolean',
            'show_in_footer' => 'boolean',
            'show_on_contact' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
