<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTableViewsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-table-views')
            ->hasViews('filament-table-views')
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
            fn () => view('filament-table-views::table-views-toolbar'),
        );
    }
}
