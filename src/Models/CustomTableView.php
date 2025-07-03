<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Dvarilek\FilamentTableViews\Contracts\ToTableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\DTO\TableViewState;

/**
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property mixed $color
 * @property bool $is_public
 * @property bool $is_favorite
 * @property bool $is_globally_highlighted
 * @property mixed $owner_id
 * @property class-string<\Illuminate\Contracts\Auth\Authenticatable & \Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership> $owner_type
 * @property class-string<\Illuminate\Database\Eloquent\Model> $model_type
 * @property \Illuminate\Database\Eloquent\Model $owner
 * @property \Illuminate\Support\Carbon | null $created_at
 * @property \Illuminate\Support\Carbon | null $updated_at
 * @property \Dvarilek\FilamentTableViews\DTO\TableViewState $view_state
 */
class CustomTableView extends Model implements ToTableView
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
        'is_globally_highlighted',
        'owner_id',
        'owner_type',
        'model_type',
        'view_state',
    ];

    protected $casts = [
        'view_state' => TableViewState::class,
    ];

    public function initializeTableView(): void
    {
        if (config('filament-table-views.custom_table_view_model.color_attribute_is_json', false)) {
            $this->mergeCasts([
                'color' => 'array',
            ]);
        }

        $this->table = config('filament-table-views.custom_table_view_model.table', 'custom_table_views');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Contracts\Auth\Authenticatable & \Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership, self>
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

    public function isGloballyHighlighted(): bool
    {
        return $this->is_globally_highlighted;
    }

    public function toTableView(): TableView
    {
        return TableView::make($this->name)
            ->icon($this->icon)
            ->color($this->color)
            ->public($this->is_public)
            ->favorite($this->is_favorite)
            ->globallyHighlighted($this->is_globally_highlighted);
    }
}
