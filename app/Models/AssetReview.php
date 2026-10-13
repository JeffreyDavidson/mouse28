<?php

namespace App\Models;

use App\Enums\AssetReviewStatus;
use App\Observers\AssetReviewObserver;
use Database\Factories\AssetReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A private, ordered image set and the decision on its current revision.
 *
 * @property list<string> $files
 * @property AssetReviewStatus $status
 * @property int $revision
 * @property int|null $reviewed_by
 */
#[Fillable(['title', 'version', 'source_post_id', 'files'])]
#[ObservedBy([AssetReviewObserver::class])]
class AssetReview extends Model
{
    /** @use HasFactory<AssetReviewFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    #[\Override]
    protected $attributes = ['status' => AssetReviewStatus::Pending->value, 'revision' => 1, 'files' => '[]'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'files' => 'array',
            'status' => AssetReviewStatus::class,
            'reviewed_at' => 'datetime',
            'revision' => 'integer',
        ];
    }

    /** @return BelongsTo<Post, $this> */
    public function sourcePost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'source_post_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Only app-generated raster paths are eligible for private delivery. */
    public function filePath(int $index): ?string
    {
        $path = $this->files[$index] ?? null;

        if (! is_string($path) || preg_match('/\Aasset-reviews\/[a-zA-Z0-9_-]+\.(png|jpe?g|webp)\z/', $path) !== 1) {
            return null;
        }

        return $path;
    }
}
