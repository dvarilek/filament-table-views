<?php

declare(strict_types = 1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Dvarilek\FilamentTableViews\Contracts\HasTableViewManager;

trait BelongsToLivewire
{
    protected HasTableViewManager $livewire;

    public function livewire(HasTableViewManager $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasTableViewManager
    {
        return $this->livewire;
    }
}
