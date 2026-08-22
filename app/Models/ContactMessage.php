<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/** @property-read string $subject_label */
#[Fillable([
    'name',
    'email',
    'subject',
    'message',
    'is_read',
])]
class ContactMessage extends Model
{
    public const SUBJECTS = [
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Podcast Guest',
        'story' => 'Share Your Story',
        'other' => 'Other',
    ];

    protected function subjectLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return self::SUBJECTS[$this->subject] ?? ucfirst($this->subject);
        });
    }

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
