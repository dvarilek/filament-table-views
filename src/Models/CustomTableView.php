<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Stancl\VirtualColumn\VirtualColumn;

/**
 * @property string $name
 * @property string|null $description
 * @property string $icon
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
 * @property array{
 *      filters: list<array{name: string, value: mixed}> | null,
 *      sort: list<array{name: string, direction: string}> | null,
 *      group: list<array{name: string}> | null
 *  } $query_constrains
 */
class CustomTableView extends Model
{
    use VirtualColumn;

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
        'query_constrains',
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

    public static function getDataColumn(): string
    {
        return 'query_constrains';
    }

    /**
     * @return list<string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'description',
            'icon',
            'color',
            'is_public',
            'is_favorite',
            'is_globally_highlighted',
            'owner_id',
            'owner_type',
            'created_at',
            'updated_at',
        ];
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
}
