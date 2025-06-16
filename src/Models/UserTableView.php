<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
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
 *
 * @property \Illuminate\Database\Eloquent\Model $owner
 *
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property array{
 *      filters: list<array{name: string, value: mixed}>,
 *      sorts: list<array{name: string, direction: string}>,
 *      groupings: list<array{name: string}>
 *  } $query_constraint_data
 */
class UserTableView extends Model
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
        'query_constraint_data'
    ];

    public function initializeTableView(): void
    {
        if (config('filament-table-views.user-table-view-model.color_attribute_is_json', false)) {
            $this->mergeCasts([
                'color' => 'array'
            ]);
        }

        $this->table = config('filament-table-views.user-table-view-model.table', 'user_table_views');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', User::class), 'owner_id');
    }

    public static function getDataColumn(): string
    {
        return 'query_constraint_data';
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