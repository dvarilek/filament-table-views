<?php

namespace Dvarilek\FilamentTableViews\Tests\Models;

use Dvarilek\FilamentTableViews\Models\Concerns\HasSavedTableViews;
use Dvarilek\FilamentTableViews\Tests\database\factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasFactory;
    use HasSavedTableViews;

    protected static string $factory = UserFactory::class;
}
