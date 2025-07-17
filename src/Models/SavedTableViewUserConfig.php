<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property bool $is_favorite
 * @property bool $is_default
 * @property int $order
 * @property mixed $saved_table_view_id
 * @property mixed $user_id
 * @property class-string<Authenticatable> $user_type
 * @property Authenticatable $user
 */
class SavedTableViewUserConfig extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'is_favorite',
        'is_default',
        'order',
        'saved_table_view_id',
        'user_id',
        'user_type',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'is_favorite' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * @return MorphTo<Authenticatable, self>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    // TODO: Link this and SavedTableView
}
