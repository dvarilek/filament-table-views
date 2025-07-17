<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Filament\Actions\DeleteAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;

class DeleteTableViewAction extends DeleteAction
{
    public static function getDefaultName(): ?string
    {
        return 'deleteTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-views::toolbar.actions.delete-table-view.label'));

        $this->action(static function (DeleteAction $action, HasTable $livewire): void {
            $result = $action->process(static fn (Model $record) => $record->delete());

            if (! $result) {
                $action->failure();

                return;
            }

            unset($livewire->userTableViews);

            $action->success();
        });
    }
}
