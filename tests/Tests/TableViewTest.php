<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Tests\Models\User;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\LivewireTableViewFixture;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can filter table using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->tableFilters->toBe([
            'status' => [
                'value' => 'active',
            ],
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableFilters->toBe([
            'status' => [
                'value' => 'inactive',
            ],
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableFilters->toBe([
            'status' => [
                'value' => null,
            ],
        ]);
});

it('can sort table using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->tableSortColumn->toBe('quantity')
        ->tableSortDirection->toBe('asc');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableSortColumn->toBe('quantity')
        ->tableSortDirection->toBe('desc');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableSortColumn->toBeNull()
        ->tableSortDirection->toBeNull();
});

it('can group table using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->tableGrouping->toBe('created_at')
        ->tableGroupingDirection->toBe('asc');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableGrouping->toBe('category')
        ->tableGroupingDirection->toBe('desc');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableGrouping->toBeNull()
        ->tableGroupingDirection->toBeNull();
});

it('can search table using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->tableSearch->toBe('search 1');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableSearch->toBe('search 2');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableSearch->toBeEmpty();
});

it('can search table columns using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->tableColumnSearches->toBe([
            'name' => 'name search 1',
            'total' => 'total search 1',
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableColumnSearches->toBe([
            'name' => 'name search 2',
            'total' => 'total search 2',
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->tableColumnSearches->toBeEmpty();
});

it('can toggle table columns using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    $originalToggledTableColumns = $livewire->instance()->toggledTableColumns;

    expect($livewire->instance())
        ->toggledTableColumns->toBe([
            'name' => false,
            'quantity' => false,
            'created_at' => true,
            'updated_at' => true,
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->toggledTableColumns->toBe([
            'name' => true,
            'quantity' => true,
            'created_at' => true,
            'updated_at' => false,
        ]);

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->toggledTableColumns->toBe($originalToggledTableColumns);

    $livewire->call('toggleActiveTableView', 'thirdTableView');

    expect($livewire->instance())
        ->toggledTableColumns->toBe([
            'name' => true,
            'quantity' => false,
            'created_at' => false,
            'updated_at' => false,
        ]);
});

it('can set active tab using table view', function () {
    $livewire = livewire(LivewireTableViewFixture::class);

    $livewire->call('toggleActiveTableView', 'firstTableView');

    expect($livewire->instance())
        ->activeTab->toBe('active');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->activeTab->toBe('inactive');

    $livewire->call('toggleActiveTableView', 'secondTableView');

    expect($livewire->instance())
        ->activeTab->toBeNull();
});
