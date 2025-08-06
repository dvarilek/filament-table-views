<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Dvarilek\FilamentTableViews\Components\TableView\BaseTableView;
use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;

trait CanManageTableViews
{
    /**
     * @var class-string<Model>|null
     */
    protected ?string $relatedModel = null;

    /**
     * @var list<TableView> | Closure
     */
    protected array|Closure $tableViews = [];

    /**
     * @var array<mixed, TableView>
     */
    protected array $cachedTableViews = [];

    protected ?Closure $modifyTableViewUsing = null;

    protected bool|Closure $shouldPersistActiveTableViewInSession = false;

    public function relatedModel(string $relatedModel): static
    {
        $this->relatedModel = $relatedModel;

        return $this;
    }

    public function tableViews(array | Closure $tableViews): static
    {
        $this->tableViews = $tableViews;

        return $this;
    }

    public function modifyTableViewUsing(?Closure $callback = null): static
    {
        $this->modifyTableViewUsing = $callback;

        return $this;
    }

    public function persistActiveTableViewInSession(bool|Closure $condition = true): static
    {
        $this->shouldPersistActiveTableViewInSession = $condition;

        return $this;
    }

    /**
     * @return class-string<Model>|null
     */
    public function getRelatedModel(): ?string
    {
        return $this->evaluate($this->relatedModel) ?? throw new Exception('Table View Manager must have a related model set using the relatedModel method.');
    }

    /**
     * @return array<mixed, TableView>
     */
    public function getTableViews(): array
    {
        return $this->cachedTableViews = array_reduce(
            $this->evaluate($this->tableViews) ?? [],
            function (array $carry, TableView $tableView) {
                $key = $tableView->getLabel();

                if ($key === null) {
                    throw new Exception('Table view must have a label set.');
                }

                $carry[$key] = $this->modifyTableView($tableView->identifier($key));

                return $carry;
            },
            []
        );
    }

    public function modifyTableView(BaseTableView $baseTableView): BaseTableView
    {
        if ($this->modifyTableViewUsing) {
            return $this->evaluate($this->modifyTableViewUsing, [
                'tableView', 'view' => $baseTableView
            ], [
                $baseTableView::class => $baseTableView
            ]) ?? $baseTableView;
        }

        return $baseTableView;
    }

    public function persistsActiveTableViewInSession(): bool
    {
        return (bool) $this->evaluate($this->shouldPersistActiveTableViewInSession) ?? config('filament-table-views.table_views.persists_active_table_view_in_session', false);
    }

    public function getActiveTableViewSessionKey(): string
    {
        $livewire = md5($this->getLivewire()::class);

        return "filament-table-views::active-table-view.{$livewire}";
    }
}
