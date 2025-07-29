<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;

trait CanResetFilters
{
    protected string|Closure|null $resetLabel = null;

    protected string|Closure|null $emptyStatePlaceholder = null;

    public function resetLabel(string|Closure|null $label): static
    {
        $this->resetLabel = $label;

        return $this;
    }

    public function emptyStatePlaceholder(string|Closure|null $placeholder): static
    {
        $this->emptyStatePlaceholder = $placeholder;

        return $this;
    }

    public function getResetLabel(): string
    {
        return $this->evaluate($this->resetLabel) ?? __('filament-table-views::toolbar.actions.manage-table-views.reset_label');
    }

    public function getEmptyStatePlaceholder(): string
    {
        if ($placeholder = $this->evaluate($this->emptyStatePlaceholder)) {
            return $placeholder;
        }

        return $this->getLivewire()->isTableViewManagerSearchEmpty()
            ? __('filament-table-views::toolbar.actions.manage-table-views.empty-state.no_views_empty_state')
            : __('filament-table-views::toolbar.actions.manage-table-views.empty-state.search_empty_state');
    }
}
