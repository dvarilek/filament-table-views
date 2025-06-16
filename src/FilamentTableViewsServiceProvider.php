<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews;

use Dvarilek\FilamentTableViews\Contracts\HasTableViews;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'filament-table-views');

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_BEFORE,
            fn () => view('filament-table-views::table-views-toolbar'),
            HasTableViews::class
        );
    }
}