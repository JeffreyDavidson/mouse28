<?php

use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Pages\SetUpRequiredMultiFactorAuthentication;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the admin dashboard', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    $user = User::factory()->admin()->create();

    $response = actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertOk();

    foreach ([WelcomeBanner::class, StatsOverview::class, RecentActivity::class, QuickDraft::class, ContentCalendar::class, InspirationWidget::class] as $widget) {
        $response->assertSeeLivewire($widget);
    }
});

test('non admin user cannot access the admin panel', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertForbidden();
});

test('unenrolled administrators must set up authentication before accessing content', function (): void {
    $user = User::factory()->admin()->withoutAppAuthentication()->create();

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertRedirect(Filament::getPanel('admin')->getSetUpRequiredMultiFactorAuthenticationUrl());

    actingAs($user)
        ->get(Filament::getPanel('admin')->getSetUpRequiredMultiFactorAuthenticationUrl() ?? throw new RuntimeException('MFA enrollment is not configured.'))
        ->assertOk()
        ->assertSee('Set up');

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $setup = livewire(SetUpRequiredMultiFactorAuthentication::class)
        ->mountAction(TestAction::make('setUpAppAuthentication')->schemaComponent(true, 'content'));
    $action = $setup->instance()->getMountedAction();
    expect($action)->not->toBeNull();
    $encrypted = $action?->getArguments()['encrypted'];
    assert(is_string($encrypted));
    $arguments = decrypt($encrypted);
    assert(is_array($arguments) && is_string($arguments['secret']));

    $setup
        ->fillForm(['code' => AppAuthentication::make()->getCurrentCode($user, $arguments['secret'])], 'mountedActionSchema0')
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(AppAuthentication::make()->isEnabled($user->refresh()))->toBeTrue()
        ->and($user->getAppAuthenticationRecoveryCodes())->toHaveCount(8);

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertOk();
});

test('administrators can manage authentication from their profile', function (): void {
    $user = User::factory()->admin()->create();

    actingAs($user)
        ->get(EditProfile::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('Regenerate recovery codes');
});

test('non administrators cannot access authentication setup', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(Filament::getPanel('admin')->getSetUpRequiredMultiFactorAuthenticationUrl() ?? throw new RuntimeException('MFA enrollment is not configured.'))
        ->assertForbidden();
});
