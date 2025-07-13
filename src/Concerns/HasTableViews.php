<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Actions\EditTableViewAction;
use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\Models\CustomTableView;
use Filament\Actions\Action;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use function Filament\Support\generate_search_term_expression;

/**
 * @mixin \Filament\Tables\Contracts\HasTable
 */
trait HasTableViews
{
    public string $tableViewManagerSearch = '';

    #[Locked]
    public array $tableViewManagerActiveFilters = [
        'default' => true,
        'favorite' => true,
        'public' => true,
        'personal' => true
    ];

    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    /**
     * @var array<string, mixed>
     */
    public array $originalToggledTableColumns = [];

    protected ?TableView $cachedActiveTableView = null;

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    public function getTableViews(): array
    {
        return [

        ];
    }

    public function getTableViewIconPosition(): ?IconPosition
    {
        return config('filament-table-views.table_views.table_view_icon_position', IconPosition::Before);
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

        $this->tableViewManagerActiveFilters[$filterButton] = !$this->tableViewManagerActiveFilters[$filterButton];
    }

    public function filterTableViewManagerItems(array $tableViews): array
    {
        return collect($tableViews)
            ->filter(fn(TableView $tableView) => str_contains(strtolower($tableView->getLabel()), strtolower($this->tableViewManagerSearch)))
            ->toArray();
    }


    public function createTableViewAction(): Action
    {
        return CreateTableViewAction::make()
            ->model($this->getTableViewModelType());
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

    public function editTableViewAction(): Action
    {
        return EditTableViewAction::make();
    }

    public function getCustomTableViewActions(): array
    {
        return [

        ];
    }

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
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
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    #[Computed(persist: true, key: 'filament-table-views::custom-table-views-computed-property')]
    public function getCustomTableViews(): array
    {
        /* @var \Illuminate\Contracts\Auth\Authenticatable | null $user */
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if (! is_subclass_of($user::class, HasTableViewOwnership::class)) {
            return [];
        }

        /* @var array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView> */
        return CustomTableView::query()
            ->whereMorphedTo('owner', $user)
            ->where('model_type', static::getTableViewModelType())
            ->get()
            ->sort(static fn (CustomTableView $a, CustomTableView $b): int => [
                    ! $a->isGloballyHighlighted(),
                    ! $a->isFavorite(),
                ] <=> [
                    ! $b->isGloballyHighlighted(),
                    ! $b->isFavorite(),
                ])
            ->mapWithKeys(static fn(CustomTableView $customTableView): array => [
                $customTableView->getKey() => $customTableView->toTableView(),
            ])
            ->toArray();
    }

    protected function getActiveTableView(): ?TableView
    {
        if ($this->cachedActiveTableView) {
            return $this->cachedActiveTableView;
        }

        if (! $this->activeTableViewKey) {
            return null;
        }

        $activeTableView = collect([
            ...$this->getDefaultTableViews(),
            ...$this->getCustomTableViews(),
        ])
            ->first(fn (TableView $tableView) => $tableView->getIdentifier() === $this->activeTableViewKey);

        return $this->cachedActiveTableView = $activeTableView;
    }

    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
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

    protected function loadStateFromTableView(TableView $tableView): void
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

        if (! $activeTableView) {
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
