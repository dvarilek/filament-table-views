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
    protected ?SavedTableView $cachedTableView = null;

    protected SavedTableView | Closure | null $tableView = null;

    public function __construct(Model | Closure | null $tableView = null)
    {
        $this->tableView($tableView);
    }

    public static function make(Model | Closure | null $tableView = null): static
    {
        $static = app(static::class, ['tableView' => $tableView]);
        $static->configure();

        return $static;
    }

    public function tableView(SavedTableView | Closure | null $tableView = null): static
    {
        $this->tableView = $tableView;

        return $this;
    }

    public function getLabel(): string
    {
        return parent::getLabel() ?? $this->getTableView()->name;
    }

    public function getIcon(): string | Htmlable | null
    {
        return parent::getIcon() ?? $this->getTableView()->icon;
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public function getColor(): string | array | null
    {
        return parent::getColor() ?? $this->getTableView()->color;
    }

    public function getIdentifier(): string
    {
        return (string) ($this->evaluate($this->identifier) ?? $this->getTableView()->getKey());
    }

    public function isPublic(): bool
    {
        return $this->getTableView()->isPublic();
    }

    public function isFavorite(): bool
    {
        return $this->getTableView()->isFavoriteForCurrentUser();
    }

    public function isDefault(): bool
    {
        return parent::isDefault() || $this->getTableView()->isDefaultForCurrentUser();
    }

    public function getTableViewState(): TableViewState
    {
        return $this->getTableView()->view_state;
    }

    public function getTableView(): SavedTableView
    {
        if ($this->cachedTableView) {
            return $this->cachedTableView;
        }

        $record = $this->evaluate($this->tableView);

        if (! $record) {
            throw new Exception('User table view must have a table view instance set.');
        }

        return $this->cachedTableView = $record;
    }
}
