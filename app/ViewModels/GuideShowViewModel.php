<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Guide;
use App\Support\ContentContinuation;
use Illuminate\Database\Eloquent\Collection;

class GuideShowViewModel
{
    /**
     * @return array{
     *     guide: Guide,
     *     relatedGuides: Collection<int, Guide>,
     *     isPreview?: true
     * }
     */
    public function data(Guide $guide, bool $preview = false): array
    {
        $guide->load('authors');

        $data = [
            'guide' => $guide,
            'relatedGuides' => ContentContinuation::relatedGuides($guide),
        ];

        if ($preview) {
            $data['isPreview'] = true;
        }

        return $data;
    }
}
