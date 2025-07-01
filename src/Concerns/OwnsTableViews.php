<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Concerns;

use Dvarilek\FilamentTableViews\Models\CustomTableView;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model & \Illuminate\Contracts\Auth\Authenticatable
 *
 * @property \Illuminate\Database\Eloquent\Collection<int, \Dvarilek\FilamentTableViews\Models\CustomTableView> $tableViews
 */
trait OwnsTableViews
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Dvarilek\FilamentTableViews\Models\CustomTableView, self>
     */
    public function tableViews(): MorphMany
    {
        return $this->morphMany(CustomTableView::class, 'owner');
    }
}
