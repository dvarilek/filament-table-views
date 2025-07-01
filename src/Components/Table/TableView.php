<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Table;

use Closure;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\HasExtraAttributes;
use Filament\Support\Concerns\HasIcon;
use Illuminate\Database\Eloquent\Builder;

class TableView extends Component
{
    use HasExtraAttributes;
    use HasIcon;

    protected string | Closure | null $label = null;

    protected string | Closure | null $tooltip = null;

    /**
     * @var string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null
     */
    protected string | array | Closure | null $color = null;

    protected ?Closure $modifyQueryUsing = null;

    public function __construct(string | Closure | null $label = null)
    {
        $this->label($label);
    }

    public static function make(string | Closure | null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        $static->configure();

        return $static;
    }

    public function label(string | Closure | null $label): static
    {
        $this->label = $label;

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

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->evaluate($this->label);
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

    public function modifyQuery(Builder $query): Builder
    {
        return $this->evaluate($this->modifyQueryUsing, [
            'query' => $query,
        ]) ?? $query;
    }
}
