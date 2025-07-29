<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;

trait CanBeSearchable
{
    protected bool|Closure $isSearchable = true;

    protected string|Closure|null $searchDebounce = '500ms';

    protected bool|Closure $searchOnBlur = false;

    protected string|Closure|null $searchLabel = null;

    protected string|Closure|null $searchPlaceholder = null;

    public function searchable(bool|Closure $condition = true): static
    {
        $this->isSearchable = $condition;

        return $this;
    }

    public function searchDebounce(string|Closure|null $debounce): static
    {
        $this->searchDebounce = $debounce;

        return $this;
    }

    public function searchOnBlur(bool|Closure $condition = true): static
    {
        $this->searchOnBlur = $condition;

        return $this;
    }

    public function searchLabel(string|Closure|null $label): static
    {
        $this->searchLabel = $label;

        return $this;
    }

    public function searchPlaceholder(string|Closure|null $placeholder): static
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    public function isSearchable(): bool
    {
        return (bool) $this->evaluate($this->isSearchable);
    }

    public function getSearchDebounce(): string
    {
        return $this->evaluate($this->searchDebounce) ?? '500ms';
    }

    public function isSearchOnBlur(): bool
    {
        return (bool) $this->evaluate($this->searchOnBlur);
    }

    public function getSearchLabel(): string
    {
        return $this->evaluate($this->searchLabel) ?? __('filament-table-views::toolbar.actions.manage-table-views.search.label');
    }

    public function getSearchPlaceholder(): string
    {
        return $this->evaluate($this->searchPlaceholder) ?? __('filament-table-views::toolbar.actions.manage-table-views.search.placeholder');
    }
}
