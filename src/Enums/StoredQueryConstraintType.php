<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Enums;

enum StoredQueryConstraintType: string
{
    case FILTERS = 'filters';

    case SORT = 'sort';

    case GROUP = 'group';
}
