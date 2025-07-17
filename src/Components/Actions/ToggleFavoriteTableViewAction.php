<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;

class ToggleFavoriteTableViewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'toggleFavoriteTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(static fn (SavedTableView $record) => $record->isFavorite()
            ? __('filament-table-views::toolbar.actions.toggle-favorite-table-view.remove_favorite_label')
            : __('filament-table-views::toolbar.actions.toggle-favorite-table-view.make_favorite_label')
        );

        $this->icon(static fn (SavedTableView $record) => $record->isFavorite() ? 'heroicon-o-x-mark' : 'heroicon-o-heart');

        $this->color('gray');

        $this->action(function (SavedTableView $record, HasTable $livewire): void {
            $record->toggleFavorite();

            unset($livewire->userTableViews);
        });
    }
}