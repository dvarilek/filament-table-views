<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Contracts;

use Dvarilek\FilamentTableViews\Components\Table\TableView;

interface ToTableView
{
    public function toTableView(): TableView;
}
