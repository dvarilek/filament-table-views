<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Manager\Concerns;

use Closure;
use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Illuminate\Database\Eloquent\Model;

trait CanBeReordered
{
    protected bool|Closure $isReorderable = true;

    protected bool|Closure $isMultiGroupReorderable = false;

    protected bool|Closure $isDeferredReorderable = true;

    protected ?Closure $checkIfGroupIsReorderableUsing = null;

    protected ?Closure $checkIfRecordIsReorderableUsing = null;

    public function reorderable(bool|Closure $isReorderable = true, bool|Closure $isDeferred = true): static
    {
        $this->isReorderable = $isReorderable;
        $this->isDeferredReorderable = $isDeferred;

        return $this;
    }

    public function multiGroupReorderable(bool|Closure $isMultiGroupReorderable = true, bool|Closure $isDeferred = true): static
    {
        $this->isMultiGroupReorderable = $isMultiGroupReorderable;
        $this->isDeferredReorderable = $isDeferred;

        return $this;
    }

    public function deferredReorderable(bool|Closure $condition = true): static
    {
        $this->isDeferredReorderable = $condition;

        return $this;
    }

    public function checkIfGroupIsReorderableUsing(?Closure $callback = null): static
    {
        $this->checkIfGroupIsReorderableUsing = $callback;

        return $this;
    }

    public function checkIfRecordIsReorderableUsing(?Closure $callback = null): static
    {
        $this->checkIfRecordIsReorderableUsing = $callback;

        return $this;
    }

    public function isReorderable(): bool
    {
        return (bool) $this->evaluate($this->isReorderable);
    }

    public function isMultiGroupReorderable(): bool
    {
        return (bool) $this->evaluate($this->isMultiGroupReorderable);
    }

    public function isDeferredReorderable(): bool
    {
        return (bool) $this->evaluate($this->isDeferredReorderable);
    }

    public function isGroupReorderable(TableViewGroupEnum $group): bool
    {
        if ($this->checkIfGroupIsReorderableUsing) {
            return (bool) $this->evaluate($this->checkIfGroupIsReorderableUsing, [
                'group' => $group,
            ], [
                TableViewGroupEnum::class => $group
            ]);
        }

        return true;
    }

    public function isRecordReorderable(SavedTableView $record): bool
    {
        if ($this->checkIfRecordIsReorderableUsing) {
            return (bool) $this->evaluate($this->checkIfRecordIsReorderableUsing, [
                'record' => $record,
                'model' => $record,
            ], [
                SavedTableView::class => $record,
                Model::class => $record
            ]);
        }

        return true;
    }
}
