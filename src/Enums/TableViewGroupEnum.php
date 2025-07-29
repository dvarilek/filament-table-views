<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Enums;

enum TableViewGroupEnum: string
{
    case FAVORITE = 'favorite';

    case PRIVATE = 'private';

    case PUBLIC = 'public';

    case SYSTEM = 'system';

    public function getGroupHeading(): string
    {
        return match ($this) {
            self::FAVORITE => __('filament-table-views::toolbar.actions.manage-table-views.groups.favorite'),
            self::PRIVATE => __('filament-table-views::toolbar.actions.manage-table-views.groups.private'),
            self::PUBLIC => __('filament-table-views::toolbar.actions.manage-table-views.groups.public'),
            self::SYSTEM => __('filament-table-views::toolbar.actions.manage-table-views.groups.system')
        };
    }

    public function getFilterLabel(): string
    {
        return match ($this) {
            self::FAVORITE => __('filament-table-views::toolbar.actions.manage-table-views.filters.favorite'),
            self::PRIVATE => __('filament-table-views::toolbar.actions.manage-table-views.filters.private'),
            self::PUBLIC => __('filament-table-views::toolbar.actions.manage-table-views.filters.public'),
            self::SYSTEM => __('filament-table-views::toolbar.actions.manage-table-views.filters.system')
        };
    }
}
