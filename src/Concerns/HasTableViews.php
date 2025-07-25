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
use Dvarilek\FilamentTableViews\Components\TableView\BaseTableView;
use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Dvarilek\FilamentTableViews\Components\TableView\UserView;
use Dvarilek\FilamentTableViews\Enums\TableViewTypeEnum;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;
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
     * @var array<value-of<TableViewTypeEnum>, bool>
     */
    #[Locked]
    public array $tableViewManagerActiveFilters = [
        TableViewTypeEnum::SYSTEM->value => true,
        TableViewTypeEnum::FAVORITE->value => true,
        TableViewTypeEnum::PUBLIC->value => true,
        TableViewTypeEnum::PRIVATE->value => true,
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

    public function getTableViewManagerSearchLabel(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.search.label');
    }

    public function getTableViewManagerSearchPlaceholder(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.search.placeholder');
    }

    public function hasTableViewManagerSearch(): bool
    {
        return true;
    }

    public function hasTableViewManagerFilterButtons(): bool
    {
        return true;
    }

    public function isTableViewManagerCollapsible(): bool
    {
        return true;
    }

    public function isTableViewManagerReorderable(): bool
    {
        return true;
    }

    public function getTableViewManagerSearchDebounce(): string
    {
        return '500ms';
    }

    public function getTableViewManagerSearchOnBlur(): bool
    {
        return false;
    }

    public function getTableViewManagerHeading(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.label');
    }

    public function getTableViewManagerWidth(): MaxWidth
    {
        return MaxWidth::Small;
    }

    public function getTableViewManagerGroupHeading(TableViewTypeEnum $group): string
    {
        return match ($group) {
            TableViewTypeEnum::FAVORITE => __('filament-table-views::toolbar.actions.manage-table-views.groups.favorite'),
            TableViewTypeEnum::PRIVATE => __('filament-table-views::toolbar.actions.manage-table-views.groups.private'),
            TableViewTypeEnum::PUBLIC => __('filament-table-views::toolbar.actions.manage-table-views.groups.public'),
            TableViewTypeEnum::SYSTEM => __('filament-table-views::toolbar.actions.manage-table-views.groups.system')
        };
    }

    public function getTableViewManagerFilterLabel(TableViewTypeEnum $group): string
    {
        return match ($group) {
            TableViewTypeEnum::FAVORITE => __('filament-table-views::toolbar.actions.manage-table-views.filters.favorite'),
            TableViewTypeEnum::PRIVATE => __('filament-table-views::toolbar.actions.manage-table-views.filters.private'),
            TableViewTypeEnum::PUBLIC => __('filament-table-views::toolbar.actions.manage-table-views.filters.public'),
            TableViewTypeEnum::SYSTEM => __('filament-table-views::toolbar.actions.manage-table-views.filters.system')
        };
    }

    public function getTableViewManagerEmptyStatePlaceholder(): ?string
    {
        return $this->tableViewManagerSearch !== ''
            ? __('filament-table-views::toolbar.actions.manage-table-views.empty-state.search_empty_state')
            : __('filament-table-views::toolbar.actions.manage-table-views.empty-state.no_views_empty_state');
    }

    public function getTableViewManagerResetLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.reset_label');
    }

    public function persistsActiveTableViewInSession(): bool
    {
        return config('filament-table-views.table_views.persists_active_table_view_in_session', false);
    }

    /**
     * @return list<TableViewTypeEnum> | Closure(TableViewTypeEnum): int
     */
    public function getTableViewManagerGroupOrder(): array | Closure
    {
        return [
            TableViewTypeEnum::FAVORITE,
            TableViewTypeEnum::PRIVATE,
            TableViewTypeEnum::PUBLIC,
            TableViewTypeEnum::SYSTEM
        ];
    }

    public function toggleViewManagerFilterButton(TableViewTypeEnum $filterButton): void
    {
        $this->tableViewManagerActiveFilters[$filterButton->value] = ! $this->tableViewManagerActiveFilters[$filterButton->value];
    }

    public function resetTableViewManager(): void
    {
        $this->tableViewManagerSearch = '';

        $this->tableViewManagerActiveFilters = [
            TableViewTypeEnum::SYSTEM->value => true,
            TableViewTypeEnum::FAVORITE->value => true,
            TableViewTypeEnum::PUBLIC->value => true,
            TableViewTypeEnum::PRIVATE->value => true,
        ];
    }

    public function reorderTableViewManagerTableViews(mixed $group, array $order): void
    {
        if (! $this->isTableViewManagerReorderable()) {
            return;
        }

        if ($group === null) {
            return;
        }

        $group = TableViewTypeEnum::tryFrom($group);

        if ($group === null) {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $tableViews = $this->groupTableViewsByType($this->userTableViews)
            ->get($group->value, collect());

        if (! $tableViews->count()) {
            return;
        }

        DB::transaction(function () use ($tableViews, $order, $user): void {
            $configRelation = $user->tableViewConfigs();

            $configModel = $configRelation->getRelated();
            $configModelKeyName = $configModel->getKeyName();
            $wrappedModelKeyName = $configModel->getConnection()?->getQueryGrammar()?->wrap($configModelKeyName) ?? $configModelKeyName;

            $configRelation
                ->whereIn('saved_table_view_id', $tableViews->pluck($tableViews->first()->getKeyName()))
                ->update([
                    'order' => DB::raw(
                        'case ' . collect($order)
                            ->map(fn ($recordKey, int $recordIndex): string => 'when ' . $wrappedModelKeyName . ' = ' . DB::getPdo()->quote($recordKey) . ' then ' . ($recordIndex + 1))
                            ->implode(' ') . ' end'
                    )
                ]);
        });

        unset($this->userTableViews);
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
    public function getSystemTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static function (TableView $tableView) {
                $key = $tableView->getLabel();

                if ($key === null) {
                    throw new Exception("Table view must have a label set.");
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
            ->sortByDesc(function (SavedTableView $tableView) {
                $config = $tableView->getCurrentAuthenticatedUserTableViewConfig();

                return $config && $config->order ? $config->order : $tableView->{$tableView->getCreatedAtColumn()};
            })
            ->mapWithKeys(static fn (SavedTableView $tableView): array => [
                (string) $tableView->getKey() => UserView::make($tableView),
            ])
            ->toArray();
    }

    /**
     * @param  bool $shouldGroupByTableViewType
     * @return ($shouldGroupByTableViewType is true ? Collection<value-of<TableViewTypeEnum>, Collection<string, BaseTableView>> : Collection<string, BaseTableView>)
     */
    protected function getAllTableViews(bool $shouldGroupByTableViewType = false): Collection
    {
        $tableViews = collect($this->getSystemTableViews() + $this->userTableViews)
            ->filter(static fn (BaseTableView $tableView) => $tableView->isVisible());

        return $shouldGroupByTableViewType ? $this->groupTableViewsByType($tableViews) : $tableViews;
    }

    /**
     * @param  Collection<string, BaseTableView> $tableViews
     * @return Collection<value-of<TableViewTypeEnum>, Collection<string, BaseTableView>>
     */
    protected function groupTableViewsByType(Collection $tableViews): Collection
    {
        return $tableViews
            ->groupBy(fn (TableView | UserView $tableView): string => match (true) {
                $tableView instanceof TableView => TableViewTypeEnum::SYSTEM->value,
                $tableView->isFavorite() => TableViewTypeEnum::FAVORITE->value,
                $tableView->isPublic() => TableViewTypeEnum::PUBLIC->value,
                default => TableViewTypeEnum::PRIVATE->value,
            }, true);
    }

    /**
     * @param  Collection<value-of<TableViewTypeEnum>, Collection<string, BaseTableView>> $tableViews
     * @return Collection<value-of<TableViewTypeEnum>, Collection<string, BaseTableView>>
     */
    protected function filterTableViewManagerItems(Collection $tableViews): Collection
    {
        return $tableViews
            ->filter(fn (Collection $tableViews, string $group) =>
                $this->tableViewManagerActiveFilters[$group] ?? false
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

    /**
     * @return class-string<Model>
     */
    protected static function getTableViewModelType(): string
    {
        return static::getResource()::getModel();
    }

    public function bootedHasTableViews(): void
    {
        $shouldPersistActiveTableViewInSession = $this->persistsActiveTableViewInSession();
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

                $this->activeTableViewKey = $defaultTableView->getIdentifier();
            }
        }
    }

    public function toggleActiveTableView(string $tableViewKey): void
    {
        $table = $this->getTable();

        $originalShouldPersistTableFiltersInSession = $table->persistsFiltersInSession();
        $originalShouldPersistTableSortInSession = $table->persistsSortInSession();
        $originalShouldPersistTableSearchInSession = $table->persistsSearchInSession();
        $originalShouldPersistTableColumnSearchesInSession = $table->persistsColumnSearchesInSession();

        // The session storage here gets explicitly disabled, so that it doesn't get polluted from updates performed
        // on livewire properties that are updated based on the active table view - only the active table view
        // should be stored in session, not its parts.
        $table
            ->persistFiltersInSession(false)
            ->persistSortInSession(false)
            ->persistSearchInSession(false)
            ->persistColumnSearchesInSession(false);

        try {
            if ($this->activeTableViewKey === $tableViewKey) {
                $this->removeActiveTableView();

                return;
            }

            $this->activeTableViewKey = $tableViewKey;

            $activeTableView = $this->getActiveTableView();

            if (! $activeTableView) {
                return;
            }

            $this->loadStateFromTableView($activeTableView);
        } finally {
            $table
                ->persistFiltersInSession($originalShouldPersistTableFiltersInSession)
                ->persistSortInSession($originalShouldPersistTableSortInSession)
                ->persistSearchInSession($originalShouldPersistTableSearchInSession)
                ->persistColumnSearchesInSession($originalShouldPersistTableColumnSearchesInSession);
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

    public function removeActiveTableView(): void
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
        if ($this->persistsActiveTableViewInSession()) {
            session()->put(
                $this->getActiveTableViewSessionKey(),
                $this->activeTableViewKey,
            );
        }

        $this->resetPage();
    }

    public function applyActiveTableViewToTableQuery(Builder $query): void
    {
        $activeTableView = $this->getActiveTableView();

        if (! ($activeTableView instanceof TableView)) {
            return;
        }

        if ($activeTableView->hasModifyQueryUsing()) {
            $activeTableView->modifyQuery($query);
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

        return "filament-table-views::active-table-view.{$table}_sort";
    }
}
