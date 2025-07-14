<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Tests\Models\Order;
use Dvarilek\FilamentTableViews\Tests\Models\User;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\LivewirePropertyFixture;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('stores DTO as JSON in the database', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(LivewirePropertyFixture::class)->instance();

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
        'tableColumnSearches' => [
            'currency' => 'dollar',
            'total' => '7',
        ],
        'toggledTableColumns' => [
            'currency' => true,
            'total' => false,
        ],
        'activeTab' => 'processing',
    ]);
});

it('casts stored JSON back to DTO', function () {
    /* @var \Filament\Tables\Contracts\HasTable $livewire */
    $livewire = livewire(LivewirePropertyFixture::class)->instance();

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
        ->tableColumnSearches->toBe($originalState->tableColumnSearches)
        ->toggledTableColumns->toBe($originalState->toggledTableColumns)
        ->activeTab->toBe($originalState->activeTab);
});

test('table view model can be converted into table view', function () {
    /* @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
    $user = auth()->user();

    $viewState = new TableViewState();

    /* @var \Dvarilek\FilamentTableViews\Models\CustomTableView $model */
    $model = $user->tableViews()->create([
        'name' => 'Test View',
        'icon' => 'heroicon-o-user',
        'color' => 'primary',
        'is_public' => false,
        'is_favorite' => true,
        'model_type' => Order::class,
        'view_state' => $viewState,
    ]);

    $tableView = $model->toTableView();

    expect($tableView)
        ->toBeInstanceOf(TableView::class)
        ->getLabel()->toBe($model->name)
        ->getIcon()->toBe($model->icon)
        ->getColor()->toBe($model->color)
        ->getIdentifier()->toBe((string) $model->getKey())
        ->isPublic()->toBe($model->is_public)
        ->isFavorite()->toBe($model->is_favorite)
        ->tableFilters($viewState->tableFilters)
        ->tableSort($viewState->tableSortColumn, $viewState->tableSortDirection)
        ->tableGrouping($viewState->tableGrouping, $viewState->tableGroupingDirection)
        ->tableSearch($viewState->tableSearch)
        ->tableColumnSearches($viewState->tableColumnSearches)
        ->toggledTableColumns($viewState->toggledTableColumns)
        ->activeTab($viewState->activeTab)
        ->hasModifyQueryUsing()->toBeFalse()
        ->and($tableView->getTableViewState())
        ->toBeInstanceOf(TableViewState::class)
        ->tableFilters->toBe($viewState->tableFilters)
        ->tableSortColumn->toBe($viewState->tableSortColumn)
        ->tableSortDirection->toBe($viewState->tableSortDirection)
        ->tableGrouping->toBe($viewState->tableGrouping)
        ->tableGroupingDirection->toBe($viewState->tableGroupingDirection)
        ->tableSearch->toBe($viewState->tableSearch)
        ->tableColumnSearches->toBe($viewState->tableColumnSearches)
        ->toggledTableColumns->toBe($viewState->toggledTableColumns)
        ->activeTab->toBe($viewState->activeTab);
});

