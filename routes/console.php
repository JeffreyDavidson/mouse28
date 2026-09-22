<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune', ['--hours' => Config::integer('telescope.retention_hours')])
    ->daily()
    ->withoutOverlapping()
    ->when(fn (): bool => Config::boolean('telescope.enabled') && Config::string('mouse28.deployment_environment') === 'staging');
