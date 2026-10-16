<?php

namespace App\Providers\Filament;

use App\Enums\ContactType;
use App\Enums\ContentType;
use App\Enums\NavigationGroup;
use App\Enums\SocialPlatform;
use App\Filament\Forms\Components\AuthorsSelect;
use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Pages\Auth\Login;
use App\Filament\Tables\Filters\MissingArtworkFilter;
use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Http\Controllers\AssetReviewFileController;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Post;
use App\Models\SocialProfile;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Forms\Components\Select;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JeffreyDavidson\CreatorKit\Filament\CreatorKitPlugin;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\EpisodeScreenOptions;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostScreenOptions;
use JeffreyDavidson\CreatorKit\Filament\Tables\Filters\MissingSeoFilter;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Every admin ability is decided by the administrator flag; capability limits live on the resources.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user): bool => $user->is_admin);

        // creator-kit's post form builds the Related Episodes select; tag it so the panel's
        // select-accessibility script repairs its listbox roles, as AuthorsSelect does.
        Select::configureUsing(function (Select $select): void {
            if ($select->getName() === 'episodes') {
                $select->extraAlpineAttributes(['data-mouse28-accessible-select' => true]);
            }
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authenticatedRoutes(function (Panel $panel): void {
                Route::get('/asset-review-files/{assetReview}/{file}', AssetReviewFileController::class)
                    ->middleware($panel->isMultiFactorAuthenticationRequired() ? [$panel->getMultiFactorAuthenticationRequiredMiddleware()] : [])
                    ->whereNumber('file')
                    ->name('assetReviews.file');
            })
            ->brandName('Mouse28')
            ->brandLogo(view('filament.brand-logo'))
            ->favicon('/images/favicon-32.png')
            ->darkMode(false)
            ->font('Poppins', provider: LocalFontProvider::class)
            ->login(Login::class)
            ->profile()
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: fn (): bool => ! app()->isLocal())
            ->spa()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::BODY_END,
                static function (): string {
                    $selectAccessibilityScript = Vite::asset('resources/js/filament/select-accessibility.js');

                    if (! Vite::isRunningHot()) {
                        $selectAccessibilityScript = parse_url($selectAccessibilityScript, PHP_URL_PATH) ?: $selectAccessibilityScript;
                    }

                    return '<script type="module" src="'.e($selectAccessibilityScript).'"></script>';
                },
            )
            ->colors([
                'primary' => '#5b3e9e',
            ])
            ->navigationGroups(NavigationGroup::class)
            ->plugin(
                CreatorKitPlugin::make()
                    ->categories(Category::class, NavigationGroup::Content, 2)
                    ->socialProfiles(SocialProfile::class, SocialPlatform::class, NavigationGroup::Settings, 2)
                    ->subscribers(NavigationGroup::Communication, 2)
                    ->newsletterIssues(NavigationGroup::Content, 3)
                    ->contactInquiries(ContactType::class, NavigationGroup::Communication, 1)
                    ->posts(Post::class, NavigationGroup::Content, 2, PostScreenOptions::make()
                        ->category(Category::class, required: true)
                        ->relatedEpisodes()
                        ->contentRequired(false)
                        ->excerptMaxLength(300)
                        ->imageField(fn (): OptimizedImageUpload => OptimizedImageUpload::make('featured_image_path')
                            ->directory('posts'))
                        ->tagsField(fn (): ContentTagsInput => ContentTagsInput::make('tags'))
                        ->authorsField(fn (): AuthorsSelect => AuthorsSelect::make('authors'))
                        ->extraColumns(fn (): array => [
                            TextColumn::make('authors.name')
                                ->label('Authors')
                                ->badge()
                                ->color('info')
                                ->placeholder('No authors'),
                        ])
                        ->extraFilters(fn (): array => [
                            SelectFilter::make('authors')
                                ->label('Author')
                                ->relationship('authors', 'name', fn (Builder $query): Builder => $query->where('users.is_author', true)),
                            MissingArtworkFilter::make(),
                            MissingSeoFilter::make(),
                        ])
                        ->reviewIntervalDays(fn (): int => Config::integer('content.post_review_interval_days'))
                        ->navigationIcon(ContentType::Post->getIcon())
                        ->globallySearchableAttributes(['title', 'slug', 'category.name', 'authors.name']))
                    ->episodes(Episode::class, NavigationGroup::Content, 1, EpisodeScreenOptions::make()
                        ->richText()
                        ->episodeNumberRequired()
                        ->descriptionRequired(false)
                        ->descriptionMaxLength(500)
                        ->imageField(fn (): OptimizedImageUpload => OptimizedImageUpload::make('featured_image_path')
                            ->directory('episodes'))
                        ->tagsField(fn (): ContentTagsInput => ContentTagsInput::make('tags'))
                        ->extraColumns(fn (): array => [
                            TextColumn::make('transcript')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Available' : 'Not available')
                                ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray')
                                ->toggleable(isToggledHiddenByDefault: true),
                        ])
                        ->extraFilters(fn (): array => [
                            MissingArtworkFilter::make(),
                            MissingSeoFilter::make(),
                        ])
                        ->navigationIcon(ContentType::Episode->getIcon())
                        ->globallySearchableAttributes(['title', 'slug', 'episode_number'])),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                WelcomeBanner::class,
                StatsOverview::class,
                RecentActivity::class,
                QuickDraft::class,
                ContentCalendar::class,
                InspirationWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
