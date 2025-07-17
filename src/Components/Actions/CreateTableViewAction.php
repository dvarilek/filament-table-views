<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;

class CreateTableViewAction extends Action
{
    use HasTableViewFormComponents;
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'createTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-views::toolbar.actions.create-table-view.label'));

        $this->modalHeading(__('filament-table-views::toolbar.actions.create-table-view.label'));

        $this->modalDescription(__('filament-table-views::toolbar.actions.create-table-view.description'));

        $this->successNotificationTitle(__('filament-table-views::toolbar.actions.create-table-view.notifications.after_table_view_created.title'));

        $this->modalSubmitActionLabel(__('filament-table-views::toolbar.actions.create-table-view.submit_label'));

        $this->iconButton();

        $this->icon('heroicon-m-plus');

        $this->color('gray');

        $this->slideOver();

        $this->modalWidth(MaxWidth::Medium);

        $this->form(static fn (CreateTableViewAction $action) => $action->getFormComponents());

        $this->action(function (HasTable $livewire): void {
            /* @var ?SavedTableView $record */
            $record = $this->process(static function (CreateTableViewAction $action, HasTable $livewire, array $data): SavedTableView {
                $tableViewModelType = $action->getModel();

                if (! $tableViewModelType) {
                    throw new Exception('The CreateViewAction must have a table view model type set.');
                }

                $user = auth()->user();

                if (! $user) {
                    throw new Exception('Cannot create TableView, user not found.');
                }

                /* @var SavedTableView */
                return $user->tableViews()->create([
                    ...$data,
                    'model_type' => $tableViewModelType,
                    'view_state' => TableViewState::fromLivewire($livewire),
                ]);
            });

            unset($this->userTableViews);

            /** @phpstan-ignore-next-line */
            $livewire->toggleActiveTableView((string) $record->getKey());

            $this->success();
        });
    }
}
