<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\TableView\Concerns;

use Closure;

trait CanBeDisabled
{
    protected bool | Closure $isDisabled = false;

    public function disabled(bool | Closure $condition = true): static
    {
        $this->isDisabled = $condition;

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->evaluate($this->isDisabled) || $this->isHidden();
    }

    public function isEnabled(): bool
    {
        return ! $this->isDisabled();
    }
}
