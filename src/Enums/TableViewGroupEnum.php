<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Enums;

enum TableViewGroupEnum: string
{
    case FAVORITE = 'favorite';

    case PRIVATE = 'private';

    case PUBLIC = 'public';

    case SYSTEM = 'system';
}
