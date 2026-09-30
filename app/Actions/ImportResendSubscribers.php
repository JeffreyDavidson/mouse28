<?php

namespace App\Actions;

use App\Models\Subscriber;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Throwable;

final class ImportResendSubscribers
{
    /**
     * Import contacts that are still subscribed in Resend as confirmed readers.
     *
     * Addresses that already have a subscriber row are never changed, so someone who
     * unsubscribed through the newsletter form stays unsubscribed. With `$apply` false
     * nothing is written and `imported` is the number that would be imported.
     *
     * @param  list<array<string, mixed>>  $contacts
     * @return array{imported: int, existing: int, unsubscribed: int, invalid: int}
     */
    public function handle(array $contacts, bool $apply): array
    {
        $summary = ['imported' => 0, 'existing' => 0, 'unsubscribed' => 0, 'invalid' => 0];
        $seen = [];

        foreach ($contacts as $contact) {
            $email = $this->email($contact['email'] ?? null);

            if ($email === null || ! array_key_exists('unsubscribed', $contact) || ! is_bool($contact['unsubscribed'])) {
                $summary['invalid']++;

                continue;
            }

            if ($contact['unsubscribed']) {
                $summary['unsubscribed']++;

                continue;
            }

            if (isset($seen[$email])) {
                continue;
            }

            $seen[$email] = true;

            if (Subscriber::query()->where('email', $email)->exists()) {
                $summary['existing']++;

                continue;
            }

            $summary['imported']++;

            if ($apply) {
                $this->store($email, $contact['created_at'] ?? null);
            }
        }

        return $summary;
    }

    private function email(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $email = Str::of($value)->trim()->lower()->toString();

        return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
    }

    private function store(string $email, mixed $createdAt): void
    {
        $joinedAt = $this->joinedAt($createdAt);

        Subscriber::query()->firstOrCreate(['email' => $email], [
            'subscribed_at' => $joinedAt,
            'verified_at' => $joinedAt,
        ]);
    }

    private function joinedAt(mixed $createdAt): CarbonInterface
    {
        if (! is_string($createdAt)) {
            return Date::now();
        }

        try {
            return Date::parse($createdAt);
        } catch (Throwable) {
            return Date::now();
        }
    }
}
