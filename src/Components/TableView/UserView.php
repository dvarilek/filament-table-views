<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\TableView;

use Closure;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class UserView extends BaseTableView
{
    protected ?SavedTableView $cachedRecord = null;

    protected SavedTableView|Closure|null $record = null;

    public function __construct(Model|Closure|null $record = null)
    {
        $this->record($record);
    }

    public static function make(Model|Closure|null $record = null): static
    {
        $static = app(static::class, ['record' => $record]);
        $static->configure();

        return $static;
    }

    public function record(SavedTableView|Closure|null $record = null): static
    {
        $this->record = $record;

        return $this;
    }

    public function getLabel(): string
    {
        return parent::getLabel() ?? $this->getRecord()->name;
    }

    public function getIcon(): string|Htmlable|null
    {
        return parent::getIcon() ?? $this->getRecord()->icon;
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public function getColor(): string|array|null
    {
        return parent::getColor() ?? $this->getRecord()->color;
    }

    public function getIdentifier(): string
    {
        return (string) ($this->evaluate($this->identifier) ?? $this->getRecord()->getKey());
    }

    public function isPublic(): bool
    {
        return $this->getRecord()->isPublic();
    }

    public function isFavorite(): bool
    {
        return $this->getRecord()->isFavoriteForCurrentUser();
    }

    public function isDefault(): bool
    {
        return parent::isDefault() || $this->getRecord()->isDefaultForCurrentUser();
    }

    public function getTableViewState(): TableViewState
    {
        return $this->getRecord()->view_state;
    }

    public function getRecord(): SavedTableView
    {
        if ($this->cachedRecord) {
            return $this->cachedRecord;
        }

        $record = $this->evaluate($this->record);

        if (! $record) {
            throw new Exception('User table view must have a table view instance set.');
        }

        return $this->cachedRecord = $record;
    }
}
