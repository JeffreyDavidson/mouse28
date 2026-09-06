<?php

namespace App\Models;

use App\Enums\ContactTopic;
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
    protected function subjectLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return ContactTopic::tryFrom($this->subject)?->getLabel() ?? ucfirst($this->subject);
        });
    }

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
