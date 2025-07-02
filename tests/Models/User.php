<?php

namespace Dvarilek\FilamentTableViews\Tests\Models;

use Dvarilek\FilamentTableViews\Concerns\OwnsTableViews;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\Tests\database\factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \Illuminate\Foundation\Auth\User implements HasTableViewOwnership
{
    use OwnsTableViews, HasFactory;

    protected static string $factory = UserFactory::class;
}