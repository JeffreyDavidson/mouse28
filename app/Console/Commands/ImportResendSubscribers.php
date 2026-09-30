<?php

namespace App\Console\Commands;

use App\Actions\ImportResendSubscribers as ImportResendSubscribersAction;
use App\Support\ResendAudience;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('newsletter:import-resend-subscribers {--apply : Write the subscribers; without it the command only reports what it would do}')]
#[Description('Import contacts that are still subscribed in the Resend audience as confirmed newsletter subscribers')]
class ImportResendSubscribers extends Command
{
    public function handle(ResendAudience $audience, ImportResendSubscribersAction $import): int
    {
        $result = $audience->refresh();

        if ($result['error'] !== null) {
            $this->error($result['error']);

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $summary = $import->handle($result['subscribers'], $apply);

        $this->line($apply ? 'Importing Resend contacts.' : 'Dry run: nothing was written. Re-run with --apply to import.');
        $this->line(($apply ? 'Imported' : 'Would import').": {$summary['imported']}");
        $this->line("Already stored: {$summary['existing']}");
        $this->line("Skipped, unsubscribed in Resend: {$summary['unsubscribed']}");
        $this->line("Skipped, unreadable contact: {$summary['invalid']}");

        return self::SUCCESS;
    }
}
