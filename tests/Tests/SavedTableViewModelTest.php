<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Components\UserView;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Dvarilek\FilamentTableViews\Tests\Models\Order;
use Dvarilek\FilamentTableViews\Tests\Models\User;
use Dvarilek\FilamentTableViews\Tests\Tests\Fixtures\LivewirePropertyFixture;
use Filament\Tables\Contracts\HasTable;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('stores DTO as JSON in the database', function () {
    /* @var HasTable $livewire */
    $livewire = livewire(LivewirePropertyFixture::class)->instance();

    $state = TableViewState::fromLivewire($livewire);

    /* @var User $user */
    $user = auth()->user();

    /* @var SavedTableView $tableView */
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
    /* @var HasTable $livewire */
    $livewire = livewire(LivewirePropertyFixture::class)->instance();

    $originalState = TableViewState::fromLivewire($livewire);

    /* @var User $user */
    $user = auth()->user();

    /* @var SavedTableView $tableView */
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
    /* @var User $user */
    $user = auth()->user();

    $viewState = new TableViewState;

    /* @var SavedTableView $model */
    $model = $user->tableViews()->create([
        'name' => 'Test View',
        'icon' => 'heroicon-o-user',
        'color' => 'primary',
        'is_public' => false,
        'is_favorite' => true,
        'model_type' => Order::class,
        'view_state' => $viewState,
    ]);

    $tableView = UserView::make($model);

    expect($tableView)
        ->toBeInstanceOf(UserView::class)
        ->getLabel()->toBe($model->name)
        ->getIcon()->toBe($model->icon)
        ->getColor()->toBe($model->color)
        ->getIdentifier()->toBe((string) $model->getKey())
        ->isPublic()->toBe($model->is_public)
        ->isFavorite()->toBe($model->is_favorite)
        ->getTableViewState()->toBe($viewState);
});
