<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Contracts;

use Dvarilek\FilamentTableViews\Components\Manager\TableViewManager;
use Dvarilek\FilamentTableViews\Components\TableView\BaseTableView;
use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Collection;

interface HasTableViewManager
{
    public function getTableViewManager(): TableViewManager;

    public function isTableViewManagerSearchEmpty(): bool;

    public function toggleActiveTableView(string $tableViewKey): void;

    /**
     * @param  Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>>  $tableViews
     * @return Collection<value-of<TableViewGroupEnum>, Collection<string, BaseTableView>>
     */
    public function filterTableViewManagerTableViews(Collection $tableViews): Collection;

    /**
     * @param  list<mixed> $order
     */
    public function reorderTableViewsInGroup(TableViewGroupEnum $group, array $order): void;

    /**
     * @param  array<value-of<TableViewGroupEnum>, list<mixed>> $groupedReorderedRecordKeys
     */
    public function reorderTableViewsInGroups(array $groupedReorderedRecordKeys): void;
}
