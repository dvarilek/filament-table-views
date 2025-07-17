<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\DeleteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\EditTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\ToggleFavoriteTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\TogglePublicTableViewAction;
use Dvarilek\FilamentTableViews\Components\TableView\BaseTableView;
use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Dvarilek\FilamentTableViews\Components\TableView\UserView;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\MaxWidth;
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
        'private' => true,
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

    public function hasTableViewManagerCollapsibleGroups(): bool
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

    public function getTableViewManagerFavoriteSectionHeading(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.sections.favorite');
    }

    public function getTableViewManagerPrivateSectionHeading(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.sections.private');
    }

    public function getTableViewManagerPublicSectionHeading(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.sections.public');
    }

    public function getTableViewManagerDefaultSectionHeading(): ?string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.sections.default');
    }

    public function getTableViewManagerEmptyStatePlaceholder(): ?string
    {
        return $this->tableViewManagerSearch !== ''
            ? __('filament-table-views::toolbar.actions.manage-table-views.empty-state.search_empty_state')
            : __('filament-table-views::toolbar.actions.manage-table-views.empty-state.no_views_empty_state');
    }

    public function getTableViewManagerFavoriteFilterLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.filters.favorite');
    }

    public function getTableViewManagerPrivateFilterLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.filters.private');
    }

    public function getTableViewManagerPublicFilterLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.filters.public');
    }

    public function getTableViewManagerDefaultFilterLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.filters.default');
    }

    public function getTableViewManagerResetLabel(): string
    {
        return __('filament-table-views::toolbar.actions.manage-table-views.reset_label');
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
            'private' => true,
        ];
    }

    /**
     * @param  array<mixed, BaseTableView>  $tableViews
     * @return array<mixed, BaseTableView>
     */
    public function filterTableViewManagerItems(array $tableViews): array // TODO: Refactor
    {
        return collect($tableViews)
            ->filter(fn (BaseTableView $tableView) => str_contains(strtolower($tableView->getLabel()), strtolower($this->tableViewManagerSearch)))
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

    public function togglePublicTableViewAction(): Action
    {
        return TogglePublicTableViewAction::make();
    }

    public function toggleFavoriteTableViewAction(): Action
    {
        return ToggleFavoriteTableViewAction::make();
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
    public function getDefaultTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static function (TableView $tableView) {
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
            ->mapWithKeys(static fn (SavedTableView $tableView): array => [
                $tableView->getKey() => UserView::make($tableView),
            ])
            ->toArray();
    }

    protected function getActiveTableView(): ?BaseTableView
    {
        if ($this->cachedActiveTableView) {
            return $this->cachedActiveTableView;
        }

        if (! $this->activeTableViewKey) {
            return null;
        }

        $activeTableView = collect([
            ...$this->getDefaultTableViews(),
            /* @phpstan-ignore-next-line */
            ...$this->userTableViews,
        ])
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
