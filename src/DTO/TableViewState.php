<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\DTO;

use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class TableViewState implements Arrayable, Castable
{
    public function __construct(
        public ?array $tableFilters = null,
        public ?string $tableSortColumn = null,
        public ?string $tableSortDirection = null,
        public ?string $tableGrouping = null,
        public ?string $tableGroupingDirection = null,
        public ?string $tableSearch = null,
        public array $toggledTableColumns = [],
        public ?string $activeTab = null,
    ) {}

    public static function fromLivewire(HasTable $livewire): TableViewState
    {
        return new TableViewState(
            tableFilters: $livewire->tableFilters,
            tableSortColumn: $livewire->getTableSortColumn(),
            tableSortDirection: $livewire->getTableSortDirection(),
            tableGrouping: $livewire->getTableGrouping()?->getId(),
            tableGroupingDirection: $livewire->getTableGroupingDirection(),
            tableSearch: $livewire->getTableSearch(),
            toggledTableColumns: $livewire->toggledTableColumns,
            activeTab: property_exists($livewire, 'activeTab') ? $livewire->activeTab : null,
        );
    }

    public function toArray(): array
    {
        return [
            'tableFilters' => $this->tableFilters,
            'tableSortColumn' => $this->tableSortColumn,
            'tableSortDirection' => $this->tableSortDirection,
            'tableGrouping' => $this->tableGrouping,
            'tableGroupingDirection' => $this->tableGroupingDirection,
            'tableSearch' => $this->tableSearch,
            'toggledTableColumns' => $this->toggledTableColumns,
            'activeTab' => $this->activeTab,
        ];
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, mixed $value, array $attributes): TableViewState
            {
                if ($value === null) {
                    return new TableViewState();
                }

                $data = json_decode($value, true, JSON_THROW_ON_ERROR);

                return new TableViewState(
                    tableFilters: $data['tableFilters'] ?? null,
                    tableSortColumn: $data['tableSortColumn'] ?? null,
                    tableSortDirection: $data['tableSortDirection'] ?? null,
                    tableGrouping: $data['tableGrouping'] ?? null,
                    tableGroupingDirection: $data['tableGroupingDirection'] ?? null,
                    tableSearch: $data['search'] ?? null,
                    toggledTableColumns: $data['toggledTableColumns'] ?? [],
                    activeTab: $data['activeTab'] ?? null,
                );
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): ?array
            {
                if ($value === null) {
                    return [$key => '{}'];
                }

                if (! $value instanceof TableViewState) {
                    throw new InvalidArgumentException('Value must be an instance of TableViewQueryConstraintsBag');
                }

                return [
                    $key => json_encode($value->toArray(), JSON_THROW_ON_ERROR),
                ];
            }
        };
    }
}
