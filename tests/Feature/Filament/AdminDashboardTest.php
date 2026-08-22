<?php

use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('authenticated user can render the admin dashboard', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    $user = User::factory()->admin()->create();

    actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSeeLivewire(WelcomeBanner::class)
        ->assertSeeLivewire(StatsOverview::class)
        ->assertSeeLivewire(RecentActivity::class)
        ->assertSeeLivewire(QuickDraft::class)
        ->assertSeeLivewire(ContentCalendar::class)
        ->assertSeeLivewire(InspirationWidget::class);
});
