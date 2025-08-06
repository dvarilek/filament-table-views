<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Dvarilek\FilamentTableViews\Components\Actions\Concerns\HasTableViewFormComponents;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;

class CreateTableViewAction extends Action
{
    use CanCustomizeProcess;
    use HasTableViewFormComponents;

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
                $relatedModel = $action->getModel();

                if (! $relatedModel) {
                    throw new Exception('The CreateViewAction must have a table related model type set.');
                }

                $user = auth()->user();

                if (! $user) {
                    throw new Exception('Cannot create TableView without an authenticated user being present.');
                }

                $isFavorite = $data['is_favorite'];
                $isDefault = $data['is_default'];
                unset($data['is_favorite'], $data['is_default']);

                /* @var SavedTableView $tableView */
                $tableView = $user->tableViews()->create([
                    ...$data,
                    'model_type' => $relatedModel,
                    'view_state' => TableViewState::fromLivewire($livewire),
                ]);

                $user->tableViewConfigs()->create([
                    'saved_table_view_id' => $tableView->getKey(),
                    'is_favorite' => $isFavorite,
                    'is_default' => $isDefault,
                ]);

                return $tableView;
            });

            unset($livewire->userTableViews);

            /** @phpstan-ignore-next-line */
            $livewire->toggleActiveTableView((string) $record->getKey());

            $this->success();
        });
    }
}
