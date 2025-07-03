<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Table;

use Closure;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\HasExtraAttributes;
use Filament\Support\Concerns\HasIcon;
use Illuminate\Database\Eloquent\Builder;

class TableView extends Component
{
    use HasExtraAttributes;
    use HasIcon;

    protected string | Closure | null $label = null;

    protected string | Closure | null $tooltip = null;

    /**
     * @var string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null
     */
    protected string | array | Closure | null $color = null;

    protected ?Closure $modifyQueryUsing = null;

    protected bool | Closure $isPublic = true;

    protected bool | Closure $isFavorite = false;

    protected bool | Closure $isGloballyHighlighted = false;

    protected array | Closure | null $tableFilters = null;

    protected string | Closure | null $tableSortColumn = null;

    protected string | Closure | null $tableSortDirection = null;

    protected string | Closure | null $tableGrouping = null;

    protected string | Closure | null $tableGroupingDirection = null;

    protected string | Closure | null $tableSearch = null;

    protected array | Closure $toggledTableColumns = [];

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

    public function label(string | Closure | null $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function tooltip(string | Closure | null $tooltip): static
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * @param  string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null  $color
     */
    public function color(string | array | Closure | null $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function public(bool | Closure $condition = true): static
    {
        $this->isPublic = $condition;

        return $this;
    }

    public function favorite(bool | Closure $condition = true): static
    {
        $this->isFavorite = $condition;

        return $this;
    }

    public function globallyHighlighted(bool | Closure $condition = true): static
    {
        $this->isGloballyHighlighted = $condition;

        return $this;
    }

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

    public function toggledTableColumns(array | Closure $toggledTableColumns = []): static
    {
        $this->toggledTableColumns = $toggledTableColumns;

        return $this;
    }

    public function activeTab(string | Closure | null $activeTab = null): static
    {
        $this->activeTab = $activeTab;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->evaluate($this->label);
    }

    public function getTooltip(): ?string
    {
        return $this->evaluate($this->tooltip);
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public function getColor(): string | array | null
    {
        return $this->evaluate($this->color) ?? 'primary';
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

    public function isPublic(): bool
    {
        return (bool) $this->evaluate($this->isPublic);
    }

    public function isFavorite(): bool
    {
        return (bool) $this->evaluate($this->isFavorite);
    }

    public function isGloballyHighlighted(): bool
    {
        return (bool) $this->evaluate($this->isGloballyHighlighted);
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
            toggledTableColumns: $this->evaluate($this->toggledTableColumns),
            activeTab: $this->evaluate($this->activeTab),
        );
    }
}
