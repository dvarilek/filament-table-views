<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property mixed $color
 * @property bool $is_public
 * @property bool $is_favorite
 * @property mixed $owner_id
 * @property class-string<Authenticatable> $owner_type
 * @property class-string<Model> $model_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property TableViewState $view_state
 * @property Authenticatable $owner
 */
class SavedTableView extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'is_public',
        'is_favorite',
        'owner_id',
        'owner_type',
        'model_type',
        'view_state',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
        'view_state' => TableViewState::class,
    ];

    public function initializeTableView(): void
    {
        if (config('filament-table-views.saved_table_view_model.color_attribute_is_json', false)) {
            $this->mergeCasts([
                'color' => 'array',
            ]);
        }
    }

    /**
     * @return MorphTo<Authenticatable, self>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isFavorite(): bool
    {
        return $this->is_favorite;
    }

    public function togglePublic(): void
    {
        $this->update(['is_public' => ! $this->is_public]);
    }

    public function toggleFavorite(): void
    {
        $this->update(['is_favorite' => ! ($this->is_favorite)]);
    }
}
