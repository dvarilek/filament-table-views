<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;
use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
use Illuminate\Support\Collection;

trait HasGroups
{
    protected ?Closure $getGroupHeadingUsing = null;

    protected bool|Closure $isCollapsible = true;

    protected ?Closure $checkIfGroupIsCollapsibleUsing = null;

    /**
     * @var list<TableViewGroupEnum>|Closure
     */
    protected array|Closure $defaultCollapsedGroups = [];

    /**
     * @var list<TableViewGroupEnum>|Closure
     */
    protected array|Closure $orderGroupsUsing = [
        TableViewGroupEnum::FAVORITE,
        TableViewGroupEnum::PRIVATE,
        TableViewGroupEnum::PUBLIC,
        TableViewGroupEnum::SYSTEM,
    ];

    public function getGroupHeadingUsing(?Closure $callback = null): static
    {
        $this->getGroupHeadingUsing = $callback;

        return $this;
    }

    public function collapsible(bool|Closure $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    public function checkIfGroupIsCollapsibleUsing(?Closure $callback = null): static
    {
        $this->checkIfGroupIsCollapsibleUsing = $callback;

        return $this;
    }

    /**
     * @param list<TableViewGroupEnum>|Closure $groups
     */
    public function defaultCollapsedGroups(array|Closure $groups = []): static
    {
        $this->defaultCollapsedGroups = $groups;

        return $this;
    }

    /**
     * @param list<TableViewGroupEnum>|Closure $order
     */
    public function orderGroups(array|Closure $order = []): static
    {
        $this->orderGroupsUsing = $order;

        return $this;
    }

    public function getGroupHeading(TableViewGroupEnum $group): ?string
    {
        if ($this->getGroupHeadingUsing) {
            return $this->evaluate($this->getGroupHeadingUsing, [
                'group' => $group,
            ], [
                TableViewGroupEnum::class => $group
            ]);
        }

        return $group->getGroupHeading();
    }

    public function isCollapsible(): bool
    {
        return (bool) $this->evaluate($this->isCollapsible);
    }

    public function isGroupCollapsible(TableViewGroupEnum $group): bool
    {
        if ($this->checkIfGroupIsCollapsibleUsing) {
            return (bool) $this->evaluate($this->checkIfGroupIsCollapsibleUsing, [
                'group' => $group,
            ], [
                TableViewGroupEnum::class => $group
            ]);
        }

        return true;
    }

    /**
     * @return list<TableViewGroupEnum>
     */
    public function getDefaultCollapsedGroups(): array
    {
        return $this->evaluate($this->defaultCollapsedGroups) ?? [];
    }

    /**
     * @return Collection<int, TableViewGroupEnum>
     */
    public function getTableViewGroups(): Collection
    {
        return collect(TableViewGroupEnum::cases())
            ->sortBy(
                $this->orderGroupsUsing instanceof Closure ?
                    $this->orderGroupsUsing :
                    fn (TableViewGroupEnum $group) => array_search($group, $this->orderGroupsUsing, true)
            )
            ->values();
    }
}
