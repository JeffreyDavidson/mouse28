<?php

namespace Database\Factories\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Gives a factory for a model that uses the SEO package a state that saves the
 * model's SEO title and description.
 */
trait HasSeoState
{
    public function withSeo(string $title = 'SEO title', string $description = 'SEO description'): static
    {
        return $this->afterCreating(function (Model $model) use ($title, $description): void {
            $model->seo->update(['title' => $title, 'description' => $description]);
        });
    }
}
