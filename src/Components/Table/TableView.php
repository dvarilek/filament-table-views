<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Table;

use Filament\Resources\Components\Tab;
use Filament\Tables\Columns\Concerns\HasColor;

class TableView extends Tab
{
    public ?string $color = null;

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColor(): string|array|null
    {
        return $this->color;
    }
}
