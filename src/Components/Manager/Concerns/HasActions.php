<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;
use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\DeleteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\EditTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\ToggleDefaultTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\ToggleFavoriteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\TogglePublicTableViewAction;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewManager;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

trait HasActions
{
    protected Action | Closure | null $createAction = null;

    protected Action | Closure | null $manageAction = null;

    protected Action | Closure | null $modifyTogglePublicTableViewActionUsing = null;

    protected Action | Closure | null $modifyToggleFavoriteTableViewActionUsing = null;

    protected Action | Closure | null $modifyToggleDefaultTableViewActionUsing = null;

    protected Action | Closure | null $modifyEditTableViewActionUsing = null;

    protected Action | Closure | null $modifyDeleteTableViewActionUsing = null;

    /**
     * @var array<Action | ActionGroup>
     */
    protected array $actions = [];

    public function createAction(Action | Closure | null $action = null): static
    {
        $this->createAction = $action;

        return $this;
    }

    public function manageAction(Action | Closure | null $action = null): static
    {
        $this->manageAction = $action;

        return $this;
    }

    public function togglePublicTableViewAction(Action | Closure | null $action = null): static
    {
        $this->modifyTogglePublicTableViewActionUsing = $action;

        return $this;
    }

    public function toggleFavoriteTableViewAction(Action | Closure | null $action = null): static
    {
        $this->modifyToggleFavoriteTableViewActionUsing = $action;

        return $this;
    }

    public function toggleDefaultTableViewAction(Action | Closure | null $action = null): static
    {
        $this->modifyToggleDefaultTableViewActionUsing = $action;

        return $this;
    }

    public function editTableViewAction(Action | Closure | null $action = null): static
    {
        $this->modifyEditTableViewActionUsing = $action;

        return $this;
    }

    public function deleteTableViewAction(Action | Closure | null $action = null): static
    {
        $this->modifyDeleteTableViewActionUsing = $action;

        return $this;
    }

    /**
     * @param array<Action | ActionGroup>|ActionGroup $actions
     */
    public function actions(array | ActionGroup $actions): static
    {
        $this->actions = [];
        $this->pushActions($actions);

        return $this;
    }

    public function getCreateAction(): ?Action
    {
        if ($this->createAction instanceof Action) {
            return $this->createAction;
        }

        $action = CreateTableViewAction::make()
            ->model($this->getRelatedModel());

        if ($this->createAction instanceof Closure) {
            return $this->evaluate($this->createAction, [
                'action', 'createAction' => $action
            ], [
                Action::class, CreateTableViewAction::class => $action,
            ]);
        }

        return $action;
    }

    public function getManageAction(): ?Action
    {
        if ($this->manageAction instanceof Action) {
            return $this->manageAction;
        }

        $action = Action::make('manageTableViews')
            ->label(__('filament-table-views::toolbar.actions.manage-table-views.label'))
            ->iconButton()
            ->icon('heroicon-m-square-3-stack-3d')
            ->color('gray')
            ->livewireClickHandlerEnabled(false);

        if ($this->manageAction instanceof Closure) {
            return $this->evaluate($this->manageAction, [
                'action', 'manageAction' => $action
            ], [
                Action::class => $action,
            ]);
        }

        return $action;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function pushActions(array | ActionGroup $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            if ($action instanceof Action) {
                $action->defaultSize(ActionSize::Small);
                $action->defaultView($action::ICON_BUTTON_VIEW);
            } elseif (! $action instanceof ActionGroup) {
                throw new InvalidArgumentException('Table view manager actions must be an instance of ' . Action::class . ' or ' . ActionGroup::class . '.');
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    public function setupActions(): void
    {
        $livewire = $this->getLivewire();

        if ($createAction = $this->getCreateAction()) {
            $livewire->cacheAction($createAction);
        }

        if ($manageAction = $this->getManageAction()) {
            $livewire->cacheAction($manageAction);
        }

        $relatedModel = $this->getRelatedModel();
        $actions = collect($this->getActions());

        while ($action = $actions->shift()) {
            if ($action instanceof ActionGroup) {
                $actions->push(...$action->getFlatActions());

                continue;
            }

            if ($action instanceof Action) {
                $action = $this->configureAction(
                    $action->record(function () use ($livewire, $relatedModel) {
                        $recordKey = array_column($livewire->mountedActionsArguments, 'filamentTableViewsRecordKey')[0] ?? null;

                        if (! $recordKey) {
                            return null;
                        }

                        $record = SavedTableView::query()->find($recordKey);

                        if ($record->model_type !== $relatedModel) {
                            return null;
                        }

                        return $record;
                    })
                );

                $livewire->cacheAction($action);

                continue;
            }

            throw new InvalidArgumentException('Table view manager actions must be an instance of ' . Action::class . ' or ' . ActionGroup::class . '.');
        }
    }

    /**
     * @param array<Action | ActionGroup> $actions
     */
    public function cacheRecordToAction(array $actions, ?SavedTableView $record = null): void
    {
        $actions = collect($actions);

        while ($action = $actions->shift()) {
            if ($action instanceof ActionGroup) {
                $actions->push(...$action->getFlatActions());

                continue;
            }

            $action
                ->record($record)
                ->arguments(['filamentTableViewsRecordKey' => $record->getKey()]);
        }
    }

    protected function configureAction(Action $action): Action
    {
        $callback = match ($action::class) {
            TogglePublicTableViewAction::class => $this->modifyTogglePublicTableViewActionUsing,
            ToggleFavoriteTableViewAction::class => $this->modifyToggleFavoriteTableViewActionUsing,
            ToggleDefaultTableViewAction::class => $this->modifyToggleDefaultTableViewActionUsing,
            EditTableViewAction::class => $this->modifyEditTableViewActionUsing,
            DeleteTableViewAction::class => $this->modifyDeleteTableViewActionUsing,
            default => null,
        };

        if ($callback) {
            return $this->evaluate($callback, [
                'action' => $action,
                $action::getDefaultName() => $action,
            ], [
                Action::class => $action,
                $action::class => $action,
            ]) ?? $action;
        }

        return $action;
    }
}
