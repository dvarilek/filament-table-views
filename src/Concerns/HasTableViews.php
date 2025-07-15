<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\EditTableViewAction;
use Dvarilek\FilamentTableViews\Components\DefaultView;
use Dvarilek\FilamentTableViews\Components\TableViewContract;
use Dvarilek\FilamentTableViews\Components\UserView;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

/**
 * @mixin HasTable
 */
trait HasTableViews
{
    public string $tableViewManagerSearch = '';

    #[Locked]
    public array $tableViewManagerActiveFilters = [
        'default' => true,
        'favorite' => true,
        'public' => true,
        'personal' => true,
    ];

    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    /**
     * @var array<string, mixed>
     */
    public array $originalToggledTableColumns = [];

    protected ?TableViewContract $cachedActiveTableView = null;

    /**
     * @return array<string, DefaultView>
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

    public function getTableViewManagerSearchDebounce(): string
    {
        return '500ms';
    }

    public function getTableViewManagerSearchOnBlur(): bool
    {
        return false;
    }

    public function persistsActiveTableViewInSession(): bool
    {
        return config('filament-table-views.table_views.persists_active_table_view_in_session', false);
    }

    public function toggleViewManagerFilterButton(string $filterButton): void
    {
        if (! array_key_exists($filterButton, $this->tableViewManagerActiveFilters)) {
            return;
        }

        $this->tableViewManagerActiveFilters[$filterButton] = ! $this->tableViewManagerActiveFilters[$filterButton];
    }

    public function resetTableViewManager(): void
    {
        $this->tableViewManagerSearch = '';

        $this->tableViewManagerActiveFilters = [
            'default' => true,
            'favorite' => true,
            'public' => true,
            'personal' => true,
        ];
    }

    /**
     * @param  array<mixed, TableViewContract>  $tableViews
     * @return array<mixed, TableViewContract>
     */
    public function filterTableViewManagerItems(array $tableViews): array
    {
        return collect($tableViews)
            ->filter(fn (TableViewContract $tableView) => str_contains(strtolower($tableView->getLabel()), strtolower($this->tableViewManagerSearch)))
            ->toArray();
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

    public function editTableViewAction(): Action
    {
        return EditTableViewAction::make();
    }

    public function deleteTableViewAction(): Action
    {
        return Action::make('todo1');
    }

    public function togglePublicAction(): Action
    {
        return Action::make('todo3');
    }

    public function toggleFavoriteAction(): Action
    {
        return Action::make('todo2');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getTableViewManagerUserActions(): array
    {
        // TODO: Finish the actions
        //       Maybe reorganize stuff here into different traits
        //       Add isVisible and isHidden to TableView
        //       Add default option (handle public and favorite) + indicator
        //       Add DefaultViews indicators, maybe consider for UserViews
        //       Reordering of views (DB persistent)
        //       TableViewManager configuration object
        //       Add broader configuration options to views (sizes, labels, allow filters, search etc.)
        //       Add option to configure from PanelServiceProvider upon registration (global and livewire / resource) instance
        //       Make sections in view manager collapsible
        //       UI for toolbar and manager + add plugin classes
        //       public / private and favorite indicators next to views in manager

        return [
            ActionGroup::make([
                $this->togglePublicAction(),
                $this->toggleFavoriteAction(),
                $this->editTableViewAction(),
                $this->deleteTableViewAction(),
            ]),
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getTableViewManagerDefaultActions(): array
    {
        return [

        ];
    }

    /**
     * @param  array<Action | ActionGroup>  $actions
     * @return array<Action | ActionGroup>
     */
    protected function processRecordToTableViewManagerActions(array $actions, ?SavedTableView $record): array
    {
        return array_filter($actions, function (Action | ActionGroup $action) use ($record) {
            if ($action instanceof ActionGroup) {
                $this->processRecordToTableViewManagerActions($action->getActions(), $record);
            } elseif ($record !== null) {
                $action->record($record);
            }

            return $action->isVisible();
        });
    }

    /**
     * @return array<string, DefaultView>
     */
    public function getDefaultTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static function (DefaultView $tableView) {
                $key = $tableView->getLabel();

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
    public function getUserTableViews(): array
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
            // 1. public & favorite
            // 2. public & not favorite
            // 3. not public & favorite
            // 4. not public & not favorite
            ->sortByDesc(static fn (SavedTableView $tableView) => (
                $tableView->isPublic() ? 2 : 0
            ) + (
                $tableView->isFavorite() ? 1 : 0
            ))
            ->mapWithKeys(static fn (SavedTableView $tableView): array => [
                $tableView->getKey() => UserView::make($tableView),
            ])
            ->toArray();
    }

    protected function getActiveTableView(): ?TableViewContract
    {
        if ($this->cachedActiveTableView) {
            return $this->cachedActiveTableView;
        }

        if (! $this->activeTableViewKey) {
            return null;
        }

        $activeTableView = collect([
            ...$this->getDefaultTableViews(),
            ...$this->getUserTableViews(),
        ])
            ->first(fn (TableViewContract $tableView) => $tableView->getIdentifier() === $this->activeTableViewKey);

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

        if (
            ($this->activeTableViewKey === null) &&
            $shouldPersistActiveTableViewInSession &&
            session()->has($activeTableViewSessionKey)
        ) {
            $this->activeTableViewKey = session()->get($activeTableViewSessionKey) ?? null;
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

    protected function loadStateFromTableView(TableViewContract $tableView): void
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

        if (! ($activeTableView instanceof DefaultView)) {
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
