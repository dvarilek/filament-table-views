<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components;

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Illuminate\Contracts\Support\Htmlable;

interface TableViewContract
{
    public function getLabel(): ?string;

    public function getIcon(): string | Htmlable | null;

    public function getTooltip(): ?string;

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string}
     */
    public function getColor(): string | array;

    public function getIdentifier(): string;

    public function getTableViewState(): TableViewState;
}
