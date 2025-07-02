<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Actions\CreateTableViewAction;
use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\Models\CustomTableView;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * @mixin \Filament\Tables\Contracts\HasTable
 */
trait HasTableViews
{
    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    public function toggleActiveTableView(string $tableViewKey): void
    {
        $this->resetTableQueryConstraints();

        if ($this->activeTableViewKey === $tableViewKey) {
            $this->activeTableViewKey = null;

            return;
        }

        $this->activeTableViewKey = $tableViewKey;
    }

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

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    public function getDefaultTableViews(): array
    {
        return collect($this->getTableViews())
            ->mapWithKeys(static fn (TableView $tableView): array => [
                $tableView->getLabel() => $tableView
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
                $customTableView->getKey() => $customTableView->toTableView()
            ])
            ->toArray();
    }

    public function resetTableQueryConstraints(): void
    {
        $this->removeTableFilters();
        $this->resetTableSearch();
        $this->resetTableColumnSearches();

        $this->tableSortDirection = null;
        $this->tableSortColumn = null;
        $this->tableGrouping = null;
        $this->tableGroupingDirection = null;
        $this->activeTab = null;
    }
}
