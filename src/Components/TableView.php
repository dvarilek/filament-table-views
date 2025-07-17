<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components;

use Closure;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TableView extends BaseTableView
{
    protected ?Closure $modifyQueryUsing = null;

    /**
     * @var array<string, mixed> | Closure | null
     */
    protected array | Closure | null $tableFilters = null;

    protected string | Closure | null $tableSortColumn = null;

    protected string | Closure | null $tableSortDirection = null;

    protected string | Closure | null $tableGrouping = null;

    protected string | Closure | null $tableGroupingDirection = null;

    protected string | Closure | null $tableSearch = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $tableColumnSearches = [];

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $toggledTableColumns = [];

    /**
     * @var list<string> | Closure
     */
    protected array | Closure $hiddenTableColumns = [];

    /**
     * @var list<string> | Closure
     */
    protected array | Closure $visibleTableColumns = [];

    protected string | Closure | null $activeTab = null;

    public function __construct(string | Closure | null $label = null)
    {
        $this->label($label);
    }

    public static function make(string | Closure | null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        $static->configure();

        return $static;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * @param  array<string, mixed> | Closure | null  $tableFilters
     * @return $this
     */
    public function tableFilters(array | Closure | null $tableFilters = null): static
    {
        $this->tableFilters = $tableFilters;

        return $this;
    }

    public function tableSort(string | Closure | null $tableSortColumn = null, string | Closure | null $tableSortDirection = null): static
    {
        $this->tableSortColumn = $tableSortColumn;
        $this->tableSortDirection = $tableSortDirection;

        return $this;
    }

    public function tableGrouping(string | Closure | null $tableGrouping = null, string | Closure | null $tableGroupingDirection = null): static
    {
        $this->tableGrouping = $tableGrouping;
        $this->tableGroupingDirection = $tableGroupingDirection;

        return $this;
    }

    public function tableSearch(string | Closure | null $tableSearch = null): static
    {
        $this->tableSearch = $tableSearch;

        return $this;
    }

    /**
     * @param  array<string, mixed> | Closure  $tableColumnSearches
     * @return $this
     */
    public function tableColumnSearches(array | Closure $tableColumnSearches = []): static
    {
        $this->tableColumnSearches = $tableColumnSearches;

        return $this;
    }

    /**
     * @param  array<string, mixed> | Closure  $columns
     * @return $this
     */
    public function toggledTableColumns(array | Closure $columns = []): static
    {
        $this->toggledTableColumns = $columns;

        return $this;
    }

    /**
     * @param  list<string> | Closure  $columns
     * @return $this
     */
    public function visibleTableColumns(array | Closure $columns = []): static
    {
        $this->visibleTableColumns = $columns;

        return $this;
    }

    /**
     * @param  list<string> | Closure  $columns
     * @return $this
     */
    public function hiddenTableColumns(array | Closure $columns = []): static
    {
        $this->hiddenTableColumns = $columns;

        return $this;
    }

    public function activeTab(string | Closure | null $activeTab = null): static
    {
        $this->activeTab = $activeTab;

        return $this;
    }

    public function modifyQuery(Builder $query): Builder
    {
        return $this->evaluate($this->modifyQueryUsing, [
            'query' => $query,
        ]) ?? $query;
    }

    public function hasModifyQueryUsing(): bool
    {
        return $this->modifyQueryUsing instanceof Closure;
    }

    public function getTableViewState(): TableViewState
    {
        return new TableViewState(
            tableFilters: $this->evaluate($this->tableFilters),
            tableSortColumn: $this->evaluate($this->tableSortColumn),
            tableSortDirection: $this->evaluate($this->tableSortDirection),
            tableGrouping: $this->evaluate($this->tableGrouping),
            tableGroupingDirection: $this->evaluate($this->tableGroupingDirection),
            tableSearch: $this->evaluate($this->tableSearch),
            tableColumnSearches: Arr::undot($this->evaluate($this->tableColumnSearches) ?? []),
            toggledTableColumns: $this->getToggledTableColumns(),
            activeTab: $this->evaluate($this->activeTab),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getToggledTableColumns(): array
    {
        $columns = Arr::undot($this->evaluate($this->toggledTableColumns) ?? []);
        $visibleColumns = $this->evaluate($this->visibleTableColumns) ?? [];
        $hiddenColumns = $this->evaluate($this->hiddenTableColumns) ?? [];

        foreach ($visibleColumns as $visibleColumn) {
            Arr::set($columns, $visibleColumn, true);
        }

        foreach ($hiddenColumns as $hiddenColumn) {
            Arr::set($columns, $hiddenColumn, false);
        }

        return $columns;
    }
}
