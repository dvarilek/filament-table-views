<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager;

use Closure;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewManager;
use Filament\Support\Components\ViewComponent;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class TableViewManager extends ViewComponent
{
    use Concerns\BelongsToLivewire;
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
    use Concerns\CanBeReordered;
    use Concerns\CanBeSearchable;
    use Concerns\CanManageTableViews;
    use Concerns\CanResetFilters;
    use Concerns\HasActions;
    use Concerns\HasFilters;
    use Concerns\HasGroups;

    protected string $view = 'filament-table-views::components.manager.index';

    protected string|Closure|null $heading = null;

    protected int|string|Closure|null $maxHeight = '500px';

    protected MaxWidth|Closure|null $width = MaxWidth::Small;

    public function __construct(string|Closure|null $heading = null)
    {
        $this->heading($heading);
    }

    public static function make(string|Closure|null $heading = null): static
    {
        $static = app(static::class, ['heading' => $heading]);
        $static->configure();

        return $static;
    }

    public function heading(string|Closure|null $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function maxHeight(int|string|Closure|null $height): static
    {
        $this->maxHeight = $height;

        return $this;
    }

    public function width(MaxWidth|Closure|null $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeading(): string
    {
        return $this->evaluate($this->heading) ?? __('filament-table-views::toolbar.actions.manage-table-views.label');
    }

    public function getMaxHeight(): string
    {
        $maxHeight = (string) $this->evaluate($this->maxHeight);

        return match (true) {
            ! $maxHeight => '500px',
            str_ends_with($maxHeight, 'px') => $maxHeight,
            default => $maxHeight . 'px',
        };
    }

    public function getWidth(): MaxWidth
    {
        return $this->evaluate($this->width) ?? MaxWidth::Small;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this->getLivewire()],
            'table' => [$this->getLivewire()->getTable()],
            'model' => [$this->getRelatedModel()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return match ($parameterType) {
            HasTable::class, HasTableViewManager::class => [$this->getLivewire()],
            Table::class => [$this->getLivewire()->getTable()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
