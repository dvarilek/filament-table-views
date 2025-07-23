<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\TableView;

use Closure;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Exception;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\HasExtraAttributes;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\HtmlString;

abstract class BaseTableView extends Component
{
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
    use HasExtraAttributes;

    protected string | Closure | null $label = null;

    protected string | Closure | null $tooltip = null;

    protected string | Htmlable | Closure | null $icon = null;

    /**
     * @var string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null
     */
    protected string | array | Closure | null $color = null;

    protected int | string | Closure | null $identifier = null;

    protected bool | Closure $isDefault = false;

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

    public function default(bool | Closure $condition = true): static
    {
        $this->isDefault = $condition;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->evaluate($this->label);
    }

    public function getIcon(): string | Htmlable | null
    {
        $icon = $this->evaluate($this->icon);

        // https://github.com/filamentphp/filament/pull/13512
        if ($icon instanceof Renderable) {
            return new HtmlString($icon->render());
        }

        return $icon;
    }

    public function getTooltip(): ?string
    {
        return $this->evaluate($this->tooltip);
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public function getColor(): string | array | null
    {
        return $this->evaluate($this->color);
    }

    public function getIdentifier(): string
    {
        $identifier = $this->evaluate($this->identifier);

        if (! $identifier) {
            $identifier = $this->getLabel();
        }

        if (! $identifier) {
            throw new Exception('A table view must have an unique identifier set to distinguish it from other table views.');
        }

        return (string) $identifier;
    }

    public function isDefault(): bool
    {
        return $this->evaluate($this->isDefault);
    }

    abstract public function getTableViewState(): TableViewState;
}
