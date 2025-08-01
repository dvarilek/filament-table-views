<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTableViewsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-table-views')
            ->hasViews('filament-table-views')
            ->hasTranslations()
            ->hasMigrations([
                'create_saved_table_views_table',
                'create_saved_table_view_user_configs',
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('dvarilek/filament-table-views');
            });
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            AlpineComponent::make('table-view-manager', __DIR__ . '/../resources/dist/js/table-view-manager.js'),
        ], 'dvarilek/filament-table-views');

        FilamentView::registerRenderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
            fn () => view('filament-table-views::table-views-toolbar'),
        );
    }
}
