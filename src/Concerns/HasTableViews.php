<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Closure;
use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\DeleteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\EditTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\ToggleDefaultTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\ToggleFavoriteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\TogglePublicTableViewAction;
use Dvarilek\FilamentTableViews\Components\Manager\TableViewManager;
use Dvarilek\FilamentTableViews\Components\TableView\BaseTableView;
use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Dvarilek\FilamentTableViews\Components\TableView\UserView;
use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

/**
 * @mixin HasTable
 */
trait HasTableViews
{
    public string $tableViewManagerSearch = '';

    /**
     * @var array<value-of<TableViewGroupEnum>, bool>
     */
    #[Locked]
    public array $tableViewManagerActiveFilters = [
        TableViewGroupEnum::SYSTEM->value => true,
        TableViewGroupEnum::FAVORITE->value => true,
        TableViewGroupEnum::PUBLIC->value => true,
        TableViewGroupEnum::PRIVATE->value => true,
    ];

    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    /**
     * @var array<string, mixed>
     */
    public array $originalToggledTableColumns = [];

    protected ?BaseTableView $cachedActiveTableView = null;

    /**
     * @return array<string, TableView>
     */
    public function getTableViews(): array
    {
        return [

        ];
    }

    public function getTableViewManager(): TableViewManager
    {
        return TableViewManager::make()
            ->livewire($this)
            ->reorderable()
            ->highlightReorderedRecords()
            ->multiGroupReorderable();
    }

    /**
     * @return class-string<Model>
     */
    protected static function getTableViewModelType(): string
    {
        return static::getResource()::getModel();
    }

    public function toggleViewManagerFilterButton(TableViewGroupEnum $group): void
    {
        $this->tableViewManagerActiveFilters[$group->value] = ! $this->tableViewManagerActiveFilters[$group->value];
    }

    public function isTableViewManagerSearchEmpty(): bool
    {
        return $this->tableViewManagerSearch === '';
    }

    public function resetTableViewManager(): void
    {
        $this->tableViewManagerSearch = '';

        $this->tableViewManagerActiveFilters = [
            TableViewGroupEnum::SYSTEM->value => true,
            TableViewGroupEnum::FAVORITE->value => true,
            TableViewGroupEnum::PUBLIC->value => true,
            TableViewGroupEnum::PRIVATE->value => true,
        ];
    }

    public function reorderTableViewsInGroup(TableViewGroupEnum $group, array $order): void
    {
        $tableViewManager = $this->getTableViewManager();

        if (! $tableViewManager->isReorderable()) {
            return;
        }

        if (! $tableViewManager->isGroupReorderable($group)) {
            return;
        }

        if (! count($order)) {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            return;
        }

        DB::transaction(function () use ($user, $order, $group, $tableViewManager) {
            $configRelation = $user->tableViewConfigs();

            $configModel = $configRelation->getRelated();
            $configModelKeyName = $configModel->getKeyName();
            $wrappedModelKeyName = $configModel->getConnection()?->getQueryGrammar()?->wrap($configModelKeyName) ?? $configModelKeyName;

            $savedTableViewForeignKeyName = $configModel->tableView()->getForeignKeyName();

            $groupTableViewKeys = $this->groupTableViewsByType(collect($this->userTableViews))
                ->get($group->value, collect())
                ->reduce(function (array $carry, UserView $userView) use ($order, $tableViewManager) {
                    $record = $userView->getRecord();

                    if (! $tableViewManager->isRecordReorderable($record)) {
                        return $carry;
                    }

                    $recordKey = $record->getKey();

                    if (in_array($recordKey, $order)) {
                        $carry[] = $recordKey;
                    }

                    return $carry;
                }, []);

            if (empty($groupTableViewKeys)) {
                return;
            }

            $configRelation
                ->whereIn($savedTableViewForeignKeyName, array_values($order))
                ->update([
                    'order' => DB::raw(
                        'case '.collect($order)
                            ->map(fn ($recordKey, int $recordIndex): string => 'when '.$wrappedModelKeyName.' = '.DB::getPdo()->quote($recordKey).' then '.($recordIndex + 1))
                            ->implode(' ').' end'
                    ),
                ]);
        });

        unset($this->userTableViews);
        $this->getTableViewsInGroupReorderedSuccessNotification()?->send();
    }

    // TODO:
    //      Make other groups visible even if they are empty
    //      Add tracking for updated table views

    /**
     * @param  array<value-of<TableViewGroupEnum>, list<mixed>> $groupedReorderedRecordKeys
     */
    public function reorderTableViewsInGroups(array $groupedReorderedRecordKeys): void
    {
        $tableViewManager = $this->getTableViewManager();

        if (! $tableViewManager->isReorderable()) {
            return;
        }

        if (! $tableViewManager->isMultiGroupReorderable()) {
            return;
        }

        if (! $user = auth()->user()) {
            return;
        }

        DB::transaction(function () use ($user, $groupedReorderedRecordKeys, $tableViewManager) {
            $configRelation = $user->tableViewConfigs();

            $configModel = $configRelation->getRelated();
            $configModelKeyName = $configModel->getKeyName();
            $wrappedModelKeyName = $configModel->getConnection()?->getQueryGrammar()?->wrap($configModelKeyName) ?? $configModelKeyName;

            $savedTableViewRelation = $configModel->tableView();
            $savedTableViewModelForeignKeyName = $savedTableViewRelation->getForeignKeyName();

            $savedTableViewModel = $savedTableViewRelation->getRelated();
            $savedTableViewModelKeyName = $savedTableViewModel->getKeyName();

            $userTableViews = collect($this->userTableViews);
            $groupedUserTableViews = $this->groupTableViewsByType($userTableViews);

            foreach ($groupedReorderedRecordKeys as $group => $order) {
                $group = TableViewGroupEnum::tryFrom($group);

                if (! $group || $group === TableViewGroupEnum::SYSTEM) {
                    continue;
                }

                if (! $tableViewManager->isGroupReorderable($group)) {
                    continue;
                }

                if (empty($order)) {
                    continue;
                }

                $authorizationMethod = match ($group) {
                    TableViewGroupEnum::FAVORITE => 'toggleFavorite',
                    TableViewGroupEnum::PUBLIC => 'togglePublic',
                    TableViewGroupEnum::PRIVATE => 'togglePrivate',
                };

                /* @var list<mixed> $originalTableViewKeys */
                $originalTableViewKeys = $groupedUserTableViews
                    ->get($group->value, collect())
                    ->map(fn (UserView $userView) => $userView->getRecord()->getKey())
                    ->toArray();

                $authorizedTableViewKeysForUpdate = $userTableViews
                    ->reduce(function (array $carry, UserView $userView) use ($order, $originalTableViewKeys, $authorizationMethod, $tableViewManager) {
                        $record = $userView->getRecord();

                        if (! $tableViewManager->isRecordReorderable($record)) {
                            return $carry;
                        }

                        $recordKey = $record->getKey();

                        if (!in_array($recordKey, $order) || in_array($recordKey, $originalTableViewKeys)) {
                            return $carry;
                        }

                        // TODO: Add authorization check here once its actually implemented
                        // if (!$this->authorizeForUser($user, $authorizationMethod, $userView->getTableView())->allowed()) {
                        //     return $carry;
                        // }

                        $carry[] = $recordKey;

                        return $carry;
                    }, []);

                if (! empty($authorizedTableViewKeysForUpdate)) {
                    $authorizedRecordsForUpdate = $configRelation
                        ->clone()
                        ->whereIn($savedTableViewModelForeignKeyName, array_values($authorizedTableViewKeysForUpdate));

                    if ($group === TableViewGroupEnum::FAVORITE) {
                        $authorizedRecordsForUpdate->update([
                            'is_favorite' => true
                        ]);
                    } else {
                        $authorizedRecordsForUpdate->update([
                            'is_favorite' => false
                        ]);

                        $savedTableViewModel
                            ->whereIn($savedTableViewModelKeyName, array_values($authorizedTableViewKeysForUpdate))
                            ->update([
                                'is_public' => $group === TableViewGroupEnum::PUBLIC
                            ]);
                    }
                }

                $configRelation
                    ->clone()
                    ->whereIn($savedTableViewModelForeignKeyName, array_values($order))
                    ->update([
                        'order' => DB::raw(
                            'case '.collect($order)
                                ->map(fn ($recordKey, int $recordIndex): string => 'when '.$wrappedModelKeyName.' = '.DB::getPdo()->quote($recordKey).' then '.($recordIndex + 1))
                                ->implode(' ').' end'
                        )
                    ]);
            }
        });

        unset($this->userTableViews);
        $this->getTableViewsInGroupReorderedSuccessNotification()?->send();
    }

    protected function getTableViewsInGroupReorderedSuccessNotification(): ?Notification
    {
        return Notification::make('tableViewsInGroupReorderedSuccessNotification')
            ->success()
            ->title(__('filament-table-views::toolbar.actions.manage-table-views.reordering.reordered_notification_title'));
    }

    public function manageTableViewsAction(): Action
    {
        return Action::make('manageTableViews')
            ->label(__('filament-table-views::toolbar.actions.manage-table-views.label'))
            ->iconButton()
            ->icon('heroicon-m-square-3-stack-3d')
            ->color('gray')
            ->livewireClickHandlerEnabled(false);
    }

    public function createTableViewAction(): Action
    {
        return CreateTableViewAction::make()
            ->model($this->getTableViewModelType());
    }

    public function togglePublicTableViewAction(): Action
    {
        return TogglePublicTableViewAction::make();
    }

    public function toggleFavoriteTableViewAction(): Action
    {
        return ToggleFavoriteTableViewAction::make();
    }

    public function toggleDefaultTableViewAction(): Action
    {
        return ToggleDefaultTableViewAction::make();
    }

    public function editTableViewAction(): Action
    {
        return EditTableViewAction::make();
    }

    public function deleteTableViewAction(): Action
    {
        return DeleteTableViewAction::make();
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getTableViewManagerUserActions(): array
    {
        return [
            ActionGroup::make([
                $this->togglePublicTableViewAction(),
                $this->toggleFavoriteTableViewAction(),
                $this->toggleDefaultTableViewAction(),
                $this->editTableViewAction(),
                $this->deleteTableViewAction(),
            ]),
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getTableViewManagerSystemActions(): array
    {
        return [

        ];
    }

    public function configureAction(Action $action): void
    {
        $recordKey = array_column($this->mountedActionsArguments, 'filamentTableViewsRecordKey')[0] ?? null;

        if (! $recordKey) {
            return;
        }

        $record = SavedTableView::query()->find($recordKey);

        if ($record->model_type !== static::getTableViewModelType()) {
            return;
        }

        $action->record($record);
    }

    /**
     * @return array<string, TableView>
     */
    protected function getSystemTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static function (TableView $tableView) {
                $key = $tableView->getLabel();

                if ($key === null) {
                    throw new Exception('Table view must have a label set.');
                }

                return [
                    $key => $tableView->identifier($key),
                ];
            })
            ->toArray();
    }

    /**
     * @return array<string, UserView>
     */
    #[Computed(persist: true, key: 'filament-table-views::user-table-views-computed-property')]
    public function userTableViews(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        /* @var array<string, UserView> */
        return SavedTableView::query()
            ->whereMorphedTo('owner', $user)
            ->where('model_type', static::getTableViewModelType())
            ->get()
            ->sortBy(function (SavedTableView $tableView) {
                $config = $tableView->getCurrentAuthenticatedUserTableViewConfig();

                if ($config?->order) {
                    return $config->order;
                }

                // Not ideal, but it's better than introducing some arbitrary value or executing an additional query
                // in attempt to get the max count for the given user.
                return PHP_INT_MAX - $tableView->{$tableView->getCreatedAtColumn()}->timestamp;
            })
            ->mapWithKeys(static fn (SavedTableView $tableView): array => [
                (string) $tableView->getKey() => UserView::make($tableView),
            ])
            ->toArray();
    }

    /**
     * @return ($shouldGroupByTableViewType is true ? Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>> : Collection<string, BaseTableView>)
     */
    public function getAllTableViews(bool $shouldGroupByTableViewType = false): Collection
    {
        $tableViews = collect($this->getSystemTableViews() + $this->userTableViews)
            ->filter(static fn (BaseTableView $tableView) => $tableView->isVisible());

        return $shouldGroupByTableViewType ? $this->groupTableViewsByType($tableViews) : $tableViews;
    }

    /**
     * @param  Collection<string, BaseTableView>  $tableViews
     * @return Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>>
     */
    protected function groupTableViewsByType(Collection $tableViews): Collection
    {
        return $tableViews
            ->groupBy(fn (TableView|UserView $tableView): string => match (true) {
                $tableView instanceof TableView => TableViewGroupEnum::SYSTEM->value,
                $tableView->isFavorite() => TableViewGroupEnum::FAVORITE->value,
                $tableView->isPublic() => TableViewGroupEnum::PUBLIC->value,
                default => TableViewGroupEnum::PRIVATE->value,
            }, true);
    }

    /**
     * @param  Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>>  $tableViews
     * @return Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>>
     */
    public function filterTableViewManagerTableViews(Collection $tableViews): Collection
    {
        return $tableViews
            ->filter(
                fn (Collection $tableViews, string $group) => $this->tableViewManagerActiveFilters[$group] ?? false
            )
            ->map(function (Collection $tableViews): Collection {
                if (empty($this->tableViewManagerSearch)) {
                    return $tableViews;
                }

                return $tableViews
                    ->filter(fn (BaseTableView $tableView): bool => str_contains(
                        strtolower($tableView->getLabel()),
                        strtolower($this->tableViewManagerSearch)
                    ));
            });
    }

    protected function getActiveTableView(): ?BaseTableView
    {
        if ($this->cachedActiveTableView) {
            return $this->cachedActiveTableView;
        }

        if (! $this->activeTableViewKey) {
            return null;
        }

        $activeTableView = $this->getAllTableViews()
            ->first(fn (BaseTableView $tableView) => $tableView->getIdentifier() === $this->activeTableViewKey);

        return $this->cachedActiveTableView = $activeTableView;
    }

    public function bootedHasTableViews(): void
    {
        $shouldPersistActiveTableViewInSession = $this->getTableViewManager()->persistsActiveTableViewInSession();
        $activeTableViewSessionKey = $this->getActiveTableViewSessionKey();

        if ($this->activeTableViewKey === null) {
            if ($shouldPersistActiveTableViewInSession && session()->has($activeTableViewSessionKey)) {
                $this->activeTableViewKey = session()->get($activeTableViewSessionKey) ?? null;
            } else {
                $defaultTableView = $this->getAllTableViews()
                    ->reduce(function ($carry, BaseTableView $tableView) {
                        if ($carry instanceof UserView && $carry->isDefault()) {
                            return $carry;
                        }

                        if ($tableView instanceof UserView && $tableView->isDefault()) {
                            return $tableView;
                        }

                        if ($carry === null && $tableView->isDefault()) {
                            return $tableView;
                        }

                        return $carry;
                    });

                $this->activeTableViewKey = $defaultTableView?->getIdentifier();
            }
        }
    }

    public function toggleActiveTableView(string $tableViewKey): void
    {
        if ($this->activeTableViewKey === $tableViewKey) {
            $this->removeActiveTableView();
        } else {
            $this->activeTableViewKey = $tableViewKey;

            $activeTableView = $this->getActiveTableView();

            if (! $activeTableView) {
                return;
            }

            $this->loadStateFromTableView($activeTableView);
        }

        $this->updatedActiveTableView();
    }

    protected function loadStateFromTableView(BaseTableView $tableView): void
    {
        $viewState = $tableView->getTableViewState();

        if (filled($this->tableFilters)) {
            if (blank($viewState->tableFilters)) {
                $this->removeTableFilters();
            } elseif ($this->tableFilters !== $viewState->tableFilters) {
                $this->tableFilters = $viewState->tableFilters;

                $this->updatedTableFilters();
            }
        }

        if (filled($this->tableSortColumn) || filled($viewState->tableSortColumn)) {
            $this->tableSortColumn = $viewState->tableSortColumn;
            $this->tableSortDirection = $viewState->tableSortDirection;

            $this->updatedTableSortColumn();
        }

        if (filled($this->tableGrouping) || filled($viewState->tableGrouping)) {
            $this->tableGrouping = $viewState->tableGrouping;
            $this->tableGroupingDirection = $viewState->tableGroupingDirection;

            $this->updatedTableGroupColumn();
        }

        if (filled($this->tableSearch) || filled($viewState->tableSearch)) {
            $this->tableSearch = $viewState->tableSearch;

            $this->updatedTableSearch();
        }

        if (
            (filled($this->tableColumnSearches) || filled($viewState->tableColumnSearches)) &&
            ($this->tableColumnSearches !== $viewState->tableColumnSearches)
        ) {
            foreach (Arr::dot($viewState->tableColumnSearches) as $column => $value) {
                Arr::set($this->tableColumnSearches, $column, $value);
            }

            $this->updatedTableColumnSearches();
        }

        // Columns are always explicitly preserved, so they can be restored when no view is active.
        // Otherwise, users might reasonably perceive this altered state as a bug.
        if (blank($this->originalToggledTableColumns)) {
            $this->originalToggledTableColumns = $this->toggledTableColumns;
        } else {
            $this->toggledTableColumns = $this->originalToggledTableColumns;
        }

        if (filled($this->toggledTableColumns) || filled($viewState->toggledTableColumns)) {
            foreach (Arr::dot($viewState->toggledTableColumns) as $column => $value) {
                Arr::set($this->toggledTableColumns, $column, $value);
            }

            $this->updatedToggledTableColumns();
        }

        if (
            property_exists($this, 'activeTab') &&
            filled($this->activeTab) || filled($viewState->activeTab) &&
            ($this->activeTab !== $viewState->activeTab)
        ) {
            $this->activeTab = $viewState->activeTab;

            if (method_exists($this, 'updatedActiveTab')) {
                $this->updatedActiveTab();
            }
        }
    }

    protected function removeActiveTableView(): void
    {
        $this->activeTableViewKey = null;

        // Table search and column searches are handled by this call.
        $this->removeTableFilters();

        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
        $this->updatedTableSortColumn();

        $this->tableGrouping = null;
        $this->tableGroupingDirection = null;
        $this->updatedTableGroupColumn();

        if (filled($this->originalToggledTableColumns)) {
            $this->toggledTableColumns = $this->originalToggledTableColumns;
            $this->originalToggledTableColumns = [];

            $this->updatedToggledTableColumns();
        }

        if (property_exists($this, 'activeTab')) {
            $this->activeTab = null;

            if (method_exists($this, 'updatedActiveTab')) {
                $this->updatedActiveTab();
            }
        }

        $this->cachedActiveTableView = null;
    }

    public function updatedActiveTableView(): void
    {
        if ($this->getTableViewManager()->persistsActiveTableViewInSession()) {
            session()->put(
                $this->getActiveTableViewSessionKey(),
                $this->activeTableViewKey,
            );
        }

        $this->resetPage();
    }

    protected function applyActiveTableViewToTableQuery(Builder $query): void
    {
        $activeTableView = $this->getActiveTableView();

        if (! ($activeTableView instanceof TableView)) {
            return;
        }

        if ($activeTableView->hasModifyQueryUsing()) {
            $activeTableView->modifyQuery($query);
        }
    }

    public function updatedTableFilters(): void
    {
        $table = $this->getTable();
        $shouldPersistTableFiltersInSession = $table->persistsFiltersInSession();

        if ($this->activeTableViewKey === null || ! $shouldPersistTableFiltersInSession) {
            parent::updatedTableFilters();

            return;
        }

        try {
            $table->persistFiltersInSession(false);

            parent::updatedTableFilters();
        } finally {
            $table->persistFiltersInSession($shouldPersistTableFiltersInSession);
        }
    }

    public function updatedTableSortColumn(): void
    {
        $table = $this->getTable();
        $shouldPersistTableSortInSession = $table->persistsSortInSession();

        if ($this->activeTableViewKey === null || ! $shouldPersistTableSortInSession) {
            parent::updatedTableSortColumn();

            return;
        }

        try {
            $table->persistSortInSession(false);

            parent::updatedTableSortColumn();
        } finally {
            $table->persistSortInSession($shouldPersistTableSortInSession);
        }
    }

    public function updatedTableSortDirection(): void
    {
        $table = $this->getTable();
        $shouldPersistTableSortInSession = $table->persistsSortInSession();

        if ($this->activeTableViewKey === null || ! $shouldPersistTableSortInSession) {
            parent::updatedTableSortDirection();

            return;
        }

        try {
            $table->persistSortInSession(false);

            parent::updatedTableSortDirection();
        } finally {
            $table->persistSortInSession($shouldPersistTableSortInSession);
        }
    }

    public function updatedTableSearch(): void
    {
        $table = $this->getTable();
        $shouldPersistTableSearchInSession = $table->persistsSearchInSession();

        if ($this->activeTableViewKey === null || ! $shouldPersistTableSearchInSession) {
            parent::updatedTableSearch();

            return;
        }

        try {
            $table->persistSearchInSession(false);

            parent::updatedTableSearch();
        } finally {
            $table->persistSearchInSession($shouldPersistTableSearchInSession);
        }
    }

    public function updatedTableColumnSearches($value = null, ?string $key = null): void
    {
        $table = $this->getTable();
        $shouldPersistColumnSearchesInSession = $table->persistsColumnSearchesInSession();

        if ($this->activeTableViewKey === null || ! $shouldPersistColumnSearchesInSession) {
            parent::updatedTableColumnSearches(...func_get_args());

            return;
        }

        try {
            $table->persistColumnSearchesInSession(false);

            parent::updatedTableColumnSearches(...func_get_args());
        } finally {
            $table->persistColumnSearchesInSession($shouldPersistColumnSearchesInSession);
        }
    }

    public function filterTableQuery(Builder $query): Builder
    {
        $this->applyActiveTableViewToTableQuery($query);

        return parent::filterTableQuery($query);
    }

    public function getActiveTableViewSessionKey(): string
    {
        $table = md5($this::class);

        return "filament-table-views::active-table-view.{$table}";
    }
}
