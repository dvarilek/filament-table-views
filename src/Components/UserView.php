<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components;

use Closure;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Exception;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\HasExtraAttributes;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class UserView extends Component implements TableViewContract
{
    use HasExtraAttributes;

    protected ?SavedTableView $cachedTableView = null;

    protected SavedTableView | Closure | null $tableView = null;

    protected string | Closure | null $label = null;

    protected string | Closure | null $tooltip = null;

    protected string | Htmlable | Closure | null $icon = null;

    /**
     * @var string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null
     */
    protected string | array | Closure | null $color = null;

    protected int | string | Closure | null $identifier = null;

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

    public function label(string | Closure | null $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string | Htmlable | Closure | null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function tooltip(string | Closure | null $tooltip): static
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * @param  string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null  $color
     */
    public function color(string | array | Closure | null $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function identifier(int | string | Closure $value): static
    {
        $this->identifier = $value;

        return $this;
    }

    public function tableView(SavedTableView | Closure | null $tableView = null): static
    {
        $this->tableView = $tableView;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->evaluate($this->label) ?? $this->getTableView()->name;
    }

    public function getIcon(): string | Htmlable | null
    {
        return $this->evaluate($this->icon) ?? $this->getTableView()->icon;
    }

    public function getTooltip(): ?string
    {
        // Don't default to description as that is probably not desirable in this case
        return $this->evaluate($this->tooltip);
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string}
     */
    public function getColor(): string | array
    {
        $icon = $this->evaluate($this->icon);

        // https://github.com/filamentphp/filament/pull/13512
        if ($icon instanceof Renderable) {
            return new HtmlString($icon->render());
        }

        return ($icon ?? $this->getTableView()->color) ?? 'primary';
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
        return $this->getTableView()->isFavorite();
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
