<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_render_the_admin_dashboard(): void
    {
        Http::fake([
            'https://api.resend.com/*' => Http::response(['data' => []]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(WelcomeBanner::class)
            ->assertSeeLivewire(StatsOverview::class)
            ->assertSeeLivewire(RecentActivity::class)
            ->assertSeeLivewire(QuickDraft::class)
            ->assertSeeLivewire(ContentCalendar::class)
            ->assertSeeLivewire(InspirationWidget::class);
    }
}
