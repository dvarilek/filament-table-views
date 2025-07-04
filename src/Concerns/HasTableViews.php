<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\CustomTableView;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * @mixin \Filament\Tables\Contracts\HasTable
 */
trait HasTableViews
{
    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    /**
     * @var array<string, mixed>
     */
    public array $cachedToggledTableColumns = [];

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    public function getTableViews(): array
    {
        return [

        ];
    }

    public function createTableViewAction(): Action
    {
        return CreateTableViewAction::make()
            ->model($this->getViewModelType());
    }

    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected static function getViewModelType(): string
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
        if ($this->activeTableViewKey === $tableViewKey) {
            $this->activeTableViewKey = null;

            $this->resetTableQueryConstraints();

            return;
        }

        $this->activeTableViewKey = $tableViewKey;

        $this->loadStateFromTableView(
            $this->getActiveTableView()
        );
    }

    protected function loadStateFromTableView(TableView $tableView): void
    {
        $viewState = $tableView->getTableViewState();

        if (filled($this->tableFilters)) {
            if (blank($viewState->tableFilters)) {
                // TODO: maybe some tableView configuration would make sense? (shouldRemoveFilters, shouldAddToTableFilters etc...)
                $this->removeTableFilters();
            } elseif ($this->tableFilters !== $viewState->tableFilters) {
                $this->tableFilters = $viewState->tableFilters;

                $this->updatedTableFilters();
            }
        }

        if (
            (filled($this->tableSortColumn) || filled($viewState->tableSortColumn)) &&
            ($this->tableSortColumn !== $viewState->tableSortColumn || $this->tableSortDirection !== $viewState->tableSortDirection)
        ) {
            $this->tableSortColumn = $viewState->tableSortColumn;
            $this->tableSortDirection = $viewState->tableSortDirection;

            $this->updatedTableSortColumn();
        }

        if (
            (filled($this->tableGrouping) || filled($viewState->tableGrouping)) &&
            ($this->tableGrouping !== $viewState->tableGrouping || $this->tableGroupingDirection !== $viewState->tableGroupingDirection)
        ) {
            $this->tableGrouping = $viewState->tableGrouping;
            $this->tableGroupingDirection = $viewState->tableGroupingDirection;

            $this->updatedTableGroupColumn();
        }

        if (
            (filled($this->tableSearch) || filled($viewState->tableSearch)) &&
            ($this->tableSearch !== $viewState->tableSearch)
        ) {
            $this->tableSearch = $viewState->tableSearch;

            $this->updatedTableSearch();
        }


        // TODO: Maybe add for other properties
        if (
            filled($this->cachedToggledTableColumns) &&
            $this->cachedToggledTableColumns !== $this->toggledTableColumns &&
            $this->cachedToggledTableColumns !== $viewState->toggledTableColumns
        ) {
            $this->toggledTableColumns = $this->cachedToggledTableColumns;

            $this->cachedToggledTableColumns = [];
        }

        if (
            filled($this->toggledTableColumns) || filled($viewState->toggledTableColumns) &&
            ($this->toggledTableColumns !== $viewState->toggledTableColumns)
        ) {
            $this->cachedToggledTableColumns = $this->toggledTableColumns;

            foreach (Arr::dot($viewState->toggledTableColumns) as $column => $value) {
                Arr::set($this->toggledTableColumns, $column, $value);
            }
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

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    public function getDefaultTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static fn (TableView $tableView): array => [
                $tableView->getLabel() => $tableView,
            ])
            ->toArray();
    }

    /**
     * @return array<mixed, \Dvarilek\FilamentTableViews\Models\CustomTableView>
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

        /* @var \Illuminate\Database\Eloquent\Builder<CustomTableView> $tableViews */
        $tableViews = $user::query()->tableViews();

        return $tableViews
            ->where('model_type', static::getResource()::getModel())
            ->get()
            ->sort(static fn (CustomTableView $a, CustomTableView $b): int => [
                    ! $a->isGloballyHighlighted(),
                    ! $a->isFavorite(),
                ] <=> [
                    ! $b->isGloballyHighlighted(),
                    ! $b->isFavorite(),
                ])
            ->mapWithKeys(static fn (CustomTableView $customTableView): array => [
                $customTableView->getKey() => $customTableView->toTableView(),
            ])
            ->toArray();
    }

    protected function getActiveTableView(): ?TableView
    {
        return collect([
            ...$this->getDefaultTableViews(),
            ...$this->getCustomTableViews(),
        ])
            ->first(fn (TableView $tableView) => $tableView->getLabel() === $this->activeTableViewKey);
    }

    public function persistsActiveTableViewInSession(): bool
    {
        return false; // TODO: From plugin provider maybe
    }

    public function getActiveTableViewSessionKey(): string
    {
        $table = md5($this::class);

        return "filament-table-views::active-table-view.{$table}_sort";
    }

    public function resetTableQueryConstraints(): void
    {
        $this->removeTableFilters();

        $this->resetTableSearch();
        $this->resetTableColumnSearches();

        $this->tableSortDirection = null;
        $this->tableSortColumn = null;
        $this->updatedTableSortColumn();

        $this->tableGrouping = null;
        $this->tableGroupingDirection = null;
        $this->updatedTableGroupColumn();

        if (
            filled($this->cachedToggledTableColumns) &&
            $this->cachedToggledTableColumns !== $this->toggledTableColumns
        ) {
            $this->toggledTableColumns = $this->cachedToggledTableColumns;

            $this->cachedToggledTableColumns = [];
        }

        if (property_exists($this, 'activeTab')) {
            $this->activeTab = null;

            if (method_exists($this, 'updatedActiveTab')) {
                $this->updatedActiveTab();
            }
        }
    }
}
