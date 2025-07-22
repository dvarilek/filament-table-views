<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property bool $is_favorite
 * @property bool $is_default
 * @property int $order
 * @property mixed $saved_table_view_id
 * @property mixed $user_id
 * @property class-string<Authenticatable> $user_type
 * @property SavedTableView $tableView
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
     * @return BelongsTo<SavedTableView, static>
     */
    public function tableView(): BelongsTo
    {
        return $this->belongsTo(SavedTableView::class, 'saved_table_view_id', 'id');
    }

    /**
     * @return MorphTo<Authenticatable, static>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function isFavorite(): bool
    {
        return $this->is_favorite;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public static function boot(): void
    {
        parent::boot();

        static::updating(static function (SavedTableViewUserConfig $config) {
            if ($config->isDirty('is_default') && $config->isDefault()) {
                static::query()
                    ->whereMorphedTo('user', $config->user)
                    ->where($config->getKeyName(), '!=', $config->getKey())
                    ->update(['is_default' => false]);
            }
        });
    }
}
