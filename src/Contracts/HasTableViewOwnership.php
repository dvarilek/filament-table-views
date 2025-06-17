<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \Dvarilek\FilamentTableViews\Models\CustomTableView> $tableViews
 */
interface HasTableViewOwnership
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Dvarilek\FilamentTableViews\Models\CustomTableView, self>
     */
    public function tableViews(): MorphMany;
}