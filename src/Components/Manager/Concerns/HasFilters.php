<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;
use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;

trait HasFilters
{
    protected bool|Closure $filterable = true;

    protected string|Closure|null $filterLabel = null;

    protected string|Closure|null $filterColor = null;

    protected string|Closure|null $filterIcon = null;

    public function filterable(bool|Closure $condition = true): static
    {
        $this->filterable = $condition;

        return $this;
    }

    public function filterLabel(string|Closure|null $label): static
    {
        $this->filterLabel = $label;

        return $this;
    }

    public function filterColor(string|Closure|null $color): static
    {
        $this->filterColor = $color;

        return $this;
    }

    public function filterIcon(string|Closure|null $icon): static
    {
        $this->filterIcon = $icon;

        return $this;
    }

    public function isFilterable(): bool
    {
        return (bool) $this->evaluate($this->filterable);
    }

    public function getFilterLabel(TableViewGroupEnum $group): string
    {
        if ($this->filterLabel) {
            return $this->evaluate($this->filterLabel, [
                'group' => $group,
            ], [
                TableViewGroupEnum::class => $group
            ]);
        }

        return $group->getFilterLabel();
    }

    public function getFilterColor(TableViewGroupEnum $group, bool $isActive): string
    {
        if ($this->filterColor) {
            return $this->evaluate($this->filterColor, [
                'group' => $group,
                'isActive' => $isActive,
            ], [
                TableViewGroupEnum::class => $group
            ]);
        }

        return $isActive ? 'primary' : 'gray';
    }

    public function getFilterIcon(TableViewGroupEnum $group, bool $isActive): string
    {
        if ($this->filterIcon) {
            return $this->evaluate($this->filterIcon, [
                'group' => $group,
                'isActive' => $isActive,
            ], [
                TableViewGroupEnum::class => $group,
            ]);
        }

        return $isActive ? 'heroicon-o-eye' : 'heroicon-o-eye-slash';
    }
}
