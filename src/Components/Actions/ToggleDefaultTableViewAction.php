<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;

class ToggleDefaultTableViewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'toggleDefaultTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            static fn (SavedTableView $record) => $record->isDefaultForCurrentUser() ?
                __('filament-table-views::toolbar.actions.toggle-favorite-table-view.remove_default_label') :
                __('filament-table-views::toolbar.actions.toggle-favorite-table-view.make_default_label')
        );

        $this->icon(static fn (SavedTableView $record) => $record->isDefaultForCurrentUser() ? 'heroicon-o-bookmark-slash' : 'heroicon-o-bookmark');

        $this->color('gray');

        $this->action(function (SavedTableView $record, HasTable $livewire): void {
            $record->toggleDefaultForCurrentUser();

            unset($livewire->userTableViews);
        });
    }
}
