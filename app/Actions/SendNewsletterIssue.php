<?php

namespace App\Actions;

use App\Jobs\DeliverNewsletterIssue;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use LogicException;

final class SendNewsletterIssue
{
    /**
     * Queue one delivery per active reader and mark the issue sent. With no active
     * readers nothing is queued and the issue is left unsent.
     *
     * @return int The number of deliveries queued.
     */
    public function handle(NewsletterIssue $issue): int
    {
        if (! $issue->isPublished()) {
            throw new LogicException('Only published newsletter issues can be sent.');
        }

        if ($issue->wasSent()) {
            throw new LogicException('This newsletter issue has already been sent.');
        }

        $queue = Queue::connection('database');

        if (! $queue instanceof DatabaseQueue || $queue->getDatabase() !== DB::connection()) {
            throw new LogicException('Newsletter deliveries must share the application database.');
        }

        // Deliveries, the sent marker, and their jobs commit together, so a failure
        // leaves nothing half sent and the issue can be sent again. The unique issue
        // and reader pair rejects a concurrent second send.
        return DB::transaction(function () use ($issue): int {
            $queued = 0;

            foreach (Subscriber::query()->active()->lazyById() as $subscriber) {
                $delivery = $issue->deliveries()->create(['subscriber_id' => $subscriber->getKey()]);

                DeliverNewsletterIssue::dispatch($delivery)
                    ->onConnection('database')
                    ->beforeCommit();
                $queued++;
            }

            // With nobody to send to, the issue stays unsent so it can be sent once readers confirm.
            if ($queued === 0) {
                return 0;
            }

            $issue->update(['sent_at' => Date::now()]);

            return $queued;
        });
    }
}
