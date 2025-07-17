<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models\Concerns;

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Dvarilek\FilamentTableViews\Models\SavedTableViewUserConfig;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin (Model & Authenticatable)
 *
 * @property Collection<int, SavedTableView> $tableViews
 * @property Collection<int, SavedTableViewUserConfig> $tableViewConfigs
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

    /**
     * @return MorphMany<SavedTableViewUserConfig, self>
     */
    public function tableViewConfigs(): MorphMany
    {
        return $this->morphMany(SavedTableViewUserConfig::class, 'user');
    }
}
