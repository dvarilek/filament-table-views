<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\TestLivewire;

use function Pest\Livewire\livewire;

it('can create a DTO from livewire', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(TestLivewire::class)->instance();

    $state = TableViewState::fromLivewire($livewire);

    expect($state)
        ->toBeInstanceOf(TableViewState::class)
        ->tableFilters->toBe([
            'trashed' => ['value' => '1'],
            'queryBuilder' => ['rules' => []],
            'client' => ['values' => ['1', '5', '8']],
            'created_at' => ['created_from' => '2025-06-30', 'created_until' => '2025-08-10'],
        ])
        ->tableSortColumn->toBe('currency')
        ->tableSortDirection->toBe('asc')
        ->tableGrouping->toBe('created_at')
        ->tableGroupingDirection->toBe('desc')
        ->tableSearch->toBe('fw')
        ->toggledTableColumns->toBe([
            'currency' => true,
            'total' => false,
        ])
        ->activeTab->toBe('processing');
});

it('can create a DTO from livewire with null values', function (string $property) {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(TestLivewire::class)->instance();
    $livewire->$property = $property === 'toggledTableColumns' ? [] : null;

    $state = TableViewState::fromLivewire($livewire);

    $property === 'toggledTableColumns'
        ? expect($state)->$property->toBeArray()
        : expect($state)->$property->toBeNull();
})->with([
    'tableFilters',
    'tableSortColumn',
    'tableSortDirection',
    'tableGrouping',
    'tableGroupingDirection',
    'tableSearch',
    'toggledTableColumns',
    'activeTab',
]);

it('can survive JSON encoding and decoding', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(TestLivewire::class)->instance();

    $originalState = TableViewState::fromLivewire($livewire);

    $jsonState = json_encode($originalState->toArray());
    $decodedState = json_decode($jsonState, true);

    expect($decodedState)->toBe([
        'tableFilters' => [
            'trashed' => ['value' => '1'],
            'queryBuilder' => ['rules' => []],
            'client' => ['values' => ['1', '5', '8']],
            'created_at' => [
                'created_from' => '2025-06-30',
                'created_until' => '2025-08-10',
            ],
        ],
        'tableSortColumn' => 'currency',
        'tableSortDirection' => 'asc',
        'tableGrouping' => 'created_at',
        'tableGroupingDirection' => 'desc',
        'tableSearch' => 'fw',
        'toggledTableColumns' => [
            'currency' => true,
            'total' => false,
        ],
        'activeTab' => 'processing',
    ]);
});