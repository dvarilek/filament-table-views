<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \Dvarilek\FilamentTableViews\Models\UserTableView> $tableViews
 */
interface HasTableViewOwnership
{
    public function tableViews(): HasMany;
}