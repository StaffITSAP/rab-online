<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WebPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('web')
            ->path('web')
            ->login(Login::class)
            ->authGuard('web')
            ->colors([
                'primary' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook('scripts.end', fn() => view('vendor.filament.components.scripts.currency-mask'))
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Detail Pengajuan RAB'),
                NavigationGroup::make()
                    ->label('Detail Perjalanan Dinas'),
                NavigationGroup::make()
                    ->label('Detail Lampiran'),
                NavigationGroup::make()
                    ->label('Pengaturan'),
            ]);
    }
}
