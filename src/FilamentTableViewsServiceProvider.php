<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews;

use App\Filament\Clusters\Products\Resources\ProductResource\Pages\ListProducts;
use Dvarilek\FilamentTableViews\Contracts\HasTableViews;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\View;
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
            TablesRenderHook::TOOLBAR_BEFORE,
            fn () => view('filament-table-views::table-views-toolbar'),
        );
    }
}
