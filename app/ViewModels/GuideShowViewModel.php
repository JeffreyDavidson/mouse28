<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Guide;
use App\Queries\RelatedGuidesQuery;
use Illuminate\Database\Eloquent\Collection;

class GuideShowViewModel
{
    public function __construct(private readonly RelatedGuidesQuery $relatedGuidesQuery) {}

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
            'relatedGuides' => $this->relatedGuidesQuery->get($guide),
        ];

        if ($preview) {
            $data['isPreview'] = true;
        }

        return $data;
    }
}
