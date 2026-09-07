<?php

namespace App\Models;

use App\Enums\ContactTopic;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'subject',
    'message',
    'is_read',
])]
class ContactMessage extends Model
{
    public function subjectLabel(): string
    {
        return ContactTopic::tryFrom($this->subject)?->getLabel() ?? ucfirst($this->subject);
    }

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
