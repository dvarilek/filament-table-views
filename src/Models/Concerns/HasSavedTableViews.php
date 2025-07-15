<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models\Concerns;

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin (Model & Authenticatable)
 *
 * @property Collection<int, SavedTableView> $tableViews
 */
trait HasSavedTableViews
{
    /**
     * @return MorphMany<SavedTableView, self>
     */
    public function tableViews(): MorphMany
    {
        return $this->morphMany(SavedTableView::class, 'owner');
    }
}
