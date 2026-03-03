<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public const SUBJECTS = [
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Podcast Guest',
        'story' => 'Share Your Story',
        'other' => 'Other',
    ];

    public function getSubjectLabelAttribute(): string
    {
        return self::SUBJECTS[$this->subject] ?? ucfirst($this->subject);
    }
}
