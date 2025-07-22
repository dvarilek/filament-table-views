<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Models;

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
 * @property Collection<int, SavedTableViewUserConfig> $tableViewUserConfigs
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
        'owner_id',
        'owner_type',
        'model_type',
        'view_state',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'view_state' => TableViewState::class,
        ];
    }

    protected ?SavedTableViewUserConfig $currentAuthenticatedUserTableViewConfig = null;

    public function initializeTableView(): void
    {
        if (config('filament-table-views.saved_table_view_model.color_attribute_is_json', false)) {
            $this->mergeCasts([
                'color' => 'array',
            ]);
        }
    }

    /**
     * @return HasMany<SavedTableViewUserConfig, static>
     */
    public function tableViewUserConfigs(): HasMany
    {
        return $this->hasMany(SavedTableViewUserConfig::class, 'saved_table_view_id', 'id');
    }

    /**
     * @return MorphTo<Authenticatable, static>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function getCurrentAuthenticatedUserTableViewConfig(): ?SavedTableViewUserConfig
    {
        if (! $this->currentAuthenticatedUserTableViewConfig) {
            $this->currentAuthenticatedUserTableViewConfig = $this->tableViewUserConfigs()
                ->whereMorphedTo('user', auth()->user())
                ->first();
        }

        return $this->currentAuthenticatedUserTableViewConfig;
    }

    public function togglePublic(): void
    {
        $this->update(['is_public' => ! $this->isPublic()]);
    }

    public function isFavoriteForCurrentUser(): bool
    {
        return $this->getCurrentAuthenticatedUserTableViewConfig()?->isFavorite() ?? false;
    }

    public function isDefaultForCurrentUser(): bool
    {
        return $this->getCurrentAuthenticatedUserTableViewConfig()?->isDefault() ?? false;
    }

    public function toggleFavoriteForCurrentUser(): void
    {
        $config = $this->getCurrentAuthenticatedUserTableViewConfig();

        if (! $config) {
            return;
        }

        $config->update([
            'is_favorite' => ! $config->isFavorite(),
        ]);
    }

    public function toggleDefaultForCurrentUser(): void
    {
        $config = $this->getCurrentAuthenticatedUserTableViewConfig();

        if (! $config) {
            return;
        }

        $config->update([
            'is_default' => ! $config->isDefault(),
        ]);
    }
}
