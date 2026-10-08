<?php

use App\Enums\NavigationGroup;
use App\Filament\Pages\PodcastSettings;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Pages\SetUpRequiredMultiFactorAuthentication;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup as PanelNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the admin dashboard', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    $user = User::factory()
        ->admin()
        ->create();

    $response = actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertOk();

    foreach ([WelcomeBanner::class, StatsOverview::class, RecentActivity::class, QuickDraft::class, ContentCalendar::class, InspirationWidget::class] as $widget) {
        $response->assertSeeLivewire($widget);
    }
});

test('admin panel registration does not require a built Vite manifest', function (): void {
    Vite::useManifestFilename('missing-manifest.json');

    try {
        $panel = new AdminPanelProvider(app())->panel(Panel::make());
    } finally {
        Vite::useManifestFilename('manifest.json');
    }

    expect($panel->getId())->toBe('admin');
});

test('admin panel registers each dashboard widget once in display order', function (): void {
    expect(array_values(Filament::getPanel('admin')->getWidgets()))->toBe([
        WelcomeBanner::class,
        StatsOverview::class,
        RecentActivity::class,
        QuickDraft::class,
        ContentCalendar::class,
        InspirationWidget::class,
    ]);
});

test('admin panel registers the navigation groups from the enum in order', function (): void {
    $labels = array_map(
        fn (PanelNavigationGroup|string $group): ?string => is_string($group) ? $group : $group->getLabel(),
        array_values(Filament::getPanel('admin')->getNavigationGroups()),
    );

    expect($labels)->toBe(['Content', 'Communication', 'Settings']);
});

test('admin panel has no dark mode brand logo while dark mode is off', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->hasDarkMode())->toBeFalse()
        ->and($panel->getDarkModeBrandLogo())
        ->toBeNull();
});

test('admin navigation keeps each item in its group and sort position', function (): void {
    $positions = [
        'Episodes' => [EpisodeResource::getNavigationGroup(), EpisodeResource::getNavigationSort()],
        'Guides' => [GuideResource::getNavigationGroup(), GuideResource::getNavigationSort()],
        'Posts' => [PostResource::getNavigationGroup(), PostResource::getNavigationSort()],
        'Categories' => [CategoryResource::getNavigationGroup(), CategoryResource::getNavigationSort()],
        'Newsletter issues' => [NewsletterIssueResource::getNavigationGroup(), NewsletterIssueResource::getNavigationSort()],
        'Contact inquiries' => [ContactInquiryResource::getNavigationGroup(), ContactInquiryResource::getNavigationSort()],
        'Subscribers' => [SubscriberResource::getNavigationGroup(), SubscriberResource::getNavigationSort()],
        'Social profiles' => [SocialProfileResource::getNavigationGroup(), SocialProfileResource::getNavigationSort()],
        'Podcast settings' => [PodcastSettings::getNavigationGroup(), PodcastSettings::getNavigationSort()],
    ];

    expect($positions)->toBe([
        'Episodes' => [NavigationGroup::Content, 1],
        'Guides' => [NavigationGroup::Content, 1],
        'Posts' => [NavigationGroup::Content, 2],
        'Categories' => [NavigationGroup::Content, 2],
        'Newsletter issues' => [NavigationGroup::Content, 3],
        'Contact inquiries' => [NavigationGroup::Communication, 1],
        'Subscribers' => [NavigationGroup::Communication, 2],
        'Social profiles' => [NavigationGroup::Settings, 2],
        'Podcast settings' => [NavigationGroup::Settings, null],
    ]);
});

test('non admin user cannot access the admin panel', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertForbidden();
});

test('an author who is not an administrator cannot access the admin panel', function (): void {
    actingAs(User::authors()->firstOrFail())
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertForbidden();
});

test('multi factor authentication is optional in the local environment', function (): void {
    app()->instance('env', 'local');

    expect(Filament::getPanel('admin')->isMultiFactorAuthenticationRequired())->toBeFalse();
});

test('multi factor authentication is required outside the local environment', function (): void {
    app()->instance('env', 'testing');

    expect(Filament::getPanel('admin')->isMultiFactorAuthenticationRequired())->toBeTrue();
});

test('unenrolled administrators must set up authentication before accessing content', function (): void {
    $user = User::factory()
        ->admin()
        ->withoutAppAuthentication()
        ->create();

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
    $action = $setup->instance()
        ->getMountedAction();
    expect($action)->not->toBeNull();
    $encrypted = $action?->getArguments()['encrypted'];
    assert(is_string($encrypted));
    $arguments = decrypt($encrypted);
    assert(is_array($arguments) && is_string($arguments['secret']));

    $setup
        ->fillForm([
            'code' => AppAuthentication::make()->getCurrentCode($user, $arguments['secret']),
            'password' => 'password',
        ], 'mountedActionSchema0')
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(AppAuthentication::make()->isEnabled($user->refresh()))->toBeTrue()
        ->and($user->getAppAuthenticationRecoveryCodes())
        ->toHaveCount(8);

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertOk();
});

test('administrators can manage authentication from their profile', function (): void {
    $user = User::factory()
        ->admin()
        ->create();

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
