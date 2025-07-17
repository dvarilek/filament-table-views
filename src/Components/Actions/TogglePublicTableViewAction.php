<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;

class TogglePublicTableViewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'togglePublicTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            static fn (SavedTableView $record) => $record->isPublic() ?
                __('filament-table-views::toolbar.actions.toggle-public-table-view.make_private_label') :
                __('filament-table-views::toolbar.actions.toggle-public-table-view.make_public_label')
        );

        $this->icon(static fn (SavedTableView $record) => $record->isPublic() ? 'heroicon-o-eye-slash' : 'heroicon-o-eye');

        $this->color('gray');

        $this->action(static function (SavedTableView $record, HasTable $livewire): void {
            $record->togglePublic();

            unset($livewire->userTableViews);
        });
    }
}
