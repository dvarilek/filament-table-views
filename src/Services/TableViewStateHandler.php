<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Services;

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Filament\Tables\Contracts\HasTable;

class TableViewStateHandler
{

    public static function applyToLivewire(TableViewState $viewState, HasTable $livewire): void
    {
        $livewire->tableFilters = $viewState->tableFilters;
        $livewire->updatedTableFilters();

        $livewire->tableSortColumn = $viewState->tableSortColumn;
        $livewire->updatedTableSortColumn();

        $livewire->tableSortDirection = $viewState->tableSortDirection;
        $livewire->updatedTableSortDirection();

        $livewire->tableGrouping = $viewState->tableGrouping;
        $livewire->tableGroupingDirection = $viewState->tableGroupingDirection;
        $livewire->updatedTableGroupColumn();

        $livewire->tableSearch = $viewState->tableSearch;
        $livewire->updatedTableSearch();

        if (property_exists($livewire, 'activeTab')) {
            $livewire->activeTab = $viewState->activeTab;
        }

        $livewire->resetPage();
    }
}
