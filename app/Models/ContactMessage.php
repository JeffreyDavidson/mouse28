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

    /**
     * Build a reply draft link, percent-encoding values so spaces stay spaces and
     * address characters such as `?` or `&` cannot add mailto header fields.
     */
    public function replyMailtoUrl(): string
    {
        $address = str_replace('%40', '@', rawurlencode($this->email));
        $subject = rawurlencode("Re: {$this->subjectLabel()}");

        return "mailto:{$address}?subject={$subject}";
    }

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'email_attempted_at' => 'datetime',
            'notification_sent_at' => 'datetime',
            'confirmation_sent_at' => 'datetime',
        ];
    }
}
