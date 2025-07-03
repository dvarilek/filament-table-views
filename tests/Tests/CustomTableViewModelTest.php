<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Tests\Models\Order;
use Dvarilek\FilamentTableViews\Tests\Models\User;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\TestLivewire;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('stores DTO as JSON in the database', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(TestLivewire::class)->instance();

    $state = TableViewState::fromLivewire($livewire);

    /* @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
    $user = auth()->user();

    /* @var \Dvarilek\FilamentTableViews\Models\CustomTableView $tableView */
    $tableView = $user->tableViews()->create([
        'name' => 'Test View',
        'model_type' => Order::class,
        'view_state' => $state,
    ]);

    $raw = $tableView->getRawOriginal('view_state');

    expect(json_decode($raw, true))->toBe([
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

it('casts stored JSON back to DTO', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(TestLivewire::class)->instance();

    $originalState = TableViewState::fromLivewire($livewire);

    /* @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
    $user = auth()->user();

    /* @var \Dvarilek\FilamentTableViews\Models\CustomTableView $tableView */
    $tableView = $user->tableViews()->create([
        'name' => 'Test View',
        'model_type' => Order::class,
        'view_state' => $originalState,
    ]);

    expect($tableView->view_state)
        ->toBeInstanceOf(TableViewState::class)
        ->tableFilters->toBe($originalState->tableFilters)
        ->tableSortColumn->toBe($originalState->tableSortColumn)
        ->tableSortDirection->toBe($originalState->tableSortDirection)
        ->tableGrouping->toBe($originalState->tableGrouping)
        ->tableGroupingDirection->toBe($originalState->tableGroupingDirection)
        ->tableSearch->toBe($originalState->tableSearch)
        ->toggledTableColumns->toBe($originalState->toggledTableColumns)
        ->activeTab->toBe($originalState->activeTab);
});

it('table view model can be converted into table view', function () {
    /* @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
    $user = auth()->user();

    /* @var \Dvarilek\FilamentTableViews\Models\CustomTableView $model */
    $model = $user->tableViews()->create([
        'name' => 'Test View',
        'icon' => 'heroicon-o-user',
        'color' => 'primary',
        'is_public' => false,
        'is_favorite' => true,
        'is_globally_highlighted' => true,
        'model_type' => Order::class,
        'view_state' => new TableViewState(),
    ]);

    expect($model->toTableView())
        ->toBeInstanceOf(TableView::class)
        ->getLabel()->toBe($model->name)
        ->getIcon()->toBe($model->icon)
        ->getColor()->toBe($model->color)
        ->isPublic()->toBe($model->is_public)
        ->isFavorite()->toBe($model->is_favorite)
        ->isGloballyHighlighted()->toBe($model->is_globally_highlighted);
});

