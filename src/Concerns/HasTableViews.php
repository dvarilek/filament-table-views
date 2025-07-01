<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\Models\CustomTableView;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * @mixin \Filament\Tables\Contracts\HasTable
 */
trait HasTableViews
{

    #[Url(as: 'tableView')]
    public ?string $activeTableViewKey = null;

    public function bootedInteractsWithTableViews(): void
    {

    }

    public function toggleActiveTableView(string $tableViewKey): void
    {
        dd($tableViewKey);

        if ($this->activeTableViewKey === $tableViewKey) {
            $this->activeTableViewKey = null;

            $this->resetTableQueryConstraints();
        } else {
            $this->activeTableViewKey = $tableViewKey;
        }
    }

    /**
     * @return array<string, \Dvarilek\FilamentTableViews\Components\Table\TableView>
     */
    public function getTableViews(): array
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
            ->mapWithKeys(fn (TableView $tableView) => [
                $tableView->getLabel() => $tableView
            ])
            ->toArray();
    }

    /**
     * @return array<mixed, \Dvarilek\FilamentTableViews\Models\CustomTableView>
     */
    #[Computed]
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
            ->sort(fn(CustomTableView $a, CustomTableView $b): int => [
                    !$a->isGloballyHighlighted(),
                    !$a->isFavorite(),
                ] <=> [
                    !$b->isGloballyHighlighted(),
                    !$b->isFavorite(),
            ])
            ->keyBy($tableViews->getModel()->getKeyName())
            ->toArray();
    }

    public function resetTableQueryConstraints(): void
    {
        $this->tableFilters
            = $this->tableSortDirection
            = $this->tableSortColumn
            = $this->tableGrouping
            = $this->tableGroupingDirection
            = $this->tableSearch
            = null;
    }
}
