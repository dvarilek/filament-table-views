<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager;

use Closure;
use Filament\Support\Components\ViewComponent;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Arr;

class TableViewManager extends ViewComponent
{
    use Concerns\BelongsToLivewire;
    use Concerns\CanBeReordered;
    use Concerns\CanBeSearchable;
    use Concerns\CanResetFilters;
    use Concerns\HasFilters;
    use Concerns\HasGroups;

    protected string $view = 'filament-table-views::components.manager.index';

    protected string|Closure|null $heading = null;

    protected MaxWidth|Closure|null $width = MaxWidth::Small;

    protected bool|Closure $shouldPersistActiveTableViewInSession = true;

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

    public function width(MaxWidth|Closure|null $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function persistActiveTableViewInSession(bool|Closure $condition = true): static
    {
        $this->shouldPersistActiveTableViewInSession = $condition;

        return $this;
    }

    public function getHeading(): string
    {
        return $this->evaluate($this->heading) ?? __('filament-table-views::toolbar.actions.manage-table-views.label');
    }

    public function getWidth(): MaxWidth
    {
        return $this->evaluate($this->width) ?? MaxWidth::Small;
    }

    public function persistsActiveTableViewInSession(): bool
    {
        return (bool) $this->evaluate($this->shouldPersistActiveTableViewInSession) ?? config('filament-table-views.table_views.persists_active_table_view_in_session', false);
    }

    public function getViewData(): array
    {
        return Arr::mapWithKeys(
            $this->viewData,
            fn (mixed $data): array => $this->evaluate($data) ?? [],
        );
    }
}
