<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use Illuminate\Support\Facades\Config;

/** Display formatting for a post's cover and the artwork shown when it has none. */
final readonly class PostPresenter
{
    public function __construct(
        private Post $post,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Post $post): self
    {
        return app()->make(self::class, ['post' => $post]);
    }

    /**
     * The fallback artwork's colours and stamp for the post's category, or the general style
     * when the category has none (categories are admin-editable, the styles are config).
     *
     * @return array{wash: string, ink: string, stamp: string}
     */
    public function artworkStyle(): array
    {
        /** @var array<string, array{wash: string, ink: string, stamp: string}> $styles */
        $styles = Config::array('mouse28.post_artwork_styles');

        return $styles[$this->post->category->slug ?? 'general'] ?? $styles['general'];
    }

    /** The cover's WebP variants; null when the post has no cover or no variants yet. */
    public function featuredImageSrcset(): ?string
    {
        return $this->images->srcset($this->post->featured_image_path);
    }
}
