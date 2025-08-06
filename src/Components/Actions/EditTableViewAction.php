<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Closure;
use Dvarilek\FilamentTableViews\Components\Actions\Concerns\HasTableViewFormComponents;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;

class EditTableViewAction extends EditAction
{
    use HasTableViewFormComponents {
        getDefaultFormComponents as baseGetDefaultFormComponents;
    }

    protected ?Closure $modifyShouldUpdateViewFormComponentUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'editTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-views::toolbar.actions.edit-table-view.label'));

        $this->modalSubmitActionLabel(__('filament-table-views::toolbar.actions.edit-table-view.submit_label'));

        $this->successNotificationTitle(__('filament-table-views::toolbar.actions.edit-table-view.notifications.after_table_view_updated.title'));

        $this->icon('heroicon-m-pencil');

        $this->color('primary');

        $this->slideOver();

        $this->modalWidth(MaxWidth::Medium);


        $this->form(static fn (EditTableViewAction $action) => $action->getFormComponents());

        $this->mutateRecordDataUsing(function ($record, $data) {
            $config = $record->getCurrentAuthenticatedUserTableViewConfig();

            return [
                ...$data,
                'is_favorite' => $config->is_favorite,
                'is_default' => $config->is_default,
            ];
        });

        $this->action(function (): void {
            $this->process(static function (EditTableViewAction $action, HasTable $livewire, SavedTableView $record, array $data): void {
                $user = auth()->user();

                if (! $user) {
                    throw new Exception('Cannot edit TableView without an authenticated user being present.');
                }

                if ($data['should_update_view'] ?? null) {
                    $data['view_state'] = TableViewState::fromLivewire($livewire);
                }

                $isFavorite = $data['is_favorite'];
                $isDefault = $data['is_default'];
                unset($data['is_favorite'], $data['is_default'], $data['should_update_view']);

                if ($translatableContentDriver = $livewire->makeFilamentTranslatableContentDriver()) {
                    $translatableContentDriver->updateRecord($record, $data);
                } else {
                    $record->update($data);
                }

                $config = $record->getCurrentAuthenticatedUserTableViewConfig();

                if ($config) {
                    $config->update([
                        'is_favorite' => $isFavorite,
                        'is_default' => $isDefault,
                    ]);
                } else {
                    $user->tableViewConfigs()->create([
                        'saved_table_view_id' => $record->getKey(),
                        'is_favorite' => $isFavorite,
                        'is_default' => $isDefault,
                    ]);
                }

                unset($livewire->userTableViews);
            });

            $this->success();
        });

    }

    public function shouldUpdateFormComponent(Closure $callback): static
    {
        $this->modifyShouldUpdateViewFormComponentUsing = $callback;

        return $this;
    }

    /**
     * @return list<Field | Component>
     */
    public function getDefaultFormComponents(): array
    {
        return [
            ...$this->baseGetDefaultFormComponents(),
            $this->getShouldUpdateViewFormComponent(),
        ];
    }

    public function getShouldUpdateViewFormComponent(): ?Field
    {
        $component = Toggle::make('should_update_view')
            ->label(__('filament-table-views::toolbar.actions.edit-table-view.form.should_update_view'));

        if ($this->modifyShouldUpdateViewFormComponentUsing) {
            $component = $this->evaluate($this->modifyShouldUpdateViewFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Toggle::class => $component,
            ]) ?? null;
        }

        return $component;
    }
}
