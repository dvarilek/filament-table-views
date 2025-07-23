<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Tests\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});
