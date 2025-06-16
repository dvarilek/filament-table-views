<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Enums;

enum StoredQueryConstraintType: string
{
    case FILTERS = 'filters';

    case SORTS = 'sorts';

    case GROUPINGS = 'groupings';
}
