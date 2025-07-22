<?php

declare(strict_types=1);


use Dvarilek\FilamentTableViews\Tests\Models\User;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\LivewireTableViewFixture;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

