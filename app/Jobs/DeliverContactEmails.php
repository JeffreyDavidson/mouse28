<?php

declare(strict_types=1);

namespace App\Jobs;

/** Retained until queued and failed payloads using the previous class name are drained. */
class DeliverContactEmails extends SendContactMessageEmails {}
