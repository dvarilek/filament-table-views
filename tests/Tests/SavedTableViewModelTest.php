<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Components\TableView\UserView;
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
        ->getTableViewState()->toBe($viewState);
});

test('isFavoriteForCurrentUser correctly determines preference based on current authenticated user config', function () {
    /* @var User $currentUser */
    $currentUser = auth()->user();

    /* @var User $otherUser */
    $otherUser = User::factory()->create();

    /* @var SavedTableView $tableView */
    $tableView = $otherUser->tableViews()->create([
        'name' => 'Public View',
        'model_type' => Order::class,
        'is_public' => true,
        'view_state' => new TableViewState,
    ]);

    expect($tableView->isFavoriteForCurrentUser())->toBeFalse();

    $otherUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_favorite' => true,
    ]);

    expect($tableView->isFavoriteForCurrentUser())->toBeFalse();

    $currentUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_favorite' => true,
    ]);

    expect($tableView->isFavoriteForCurrentUser())->toBeTrue();
});

test('isDefaultForCurrentUser correctly determines preference based on current authenticated user config', function () {
    /* @var User $currentUser */
    $currentUser = auth()->user();

    /* @var User $otherUser */
    $otherUser = User::factory()->create();

    /* @var SavedTableView $tableView */
    $tableView = $otherUser->tableViews()->create([
        'name' => 'Public View',
        'model_type' => Order::class,
        'is_public' => true,
        'view_state' => new TableViewState,
    ]);

    expect($tableView->isDefaultForCurrentUser())->toBeFalse();

    $otherUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_default' => true,
    ]);

    expect($tableView->isDefaultForCurrentUser())->toBeFalse();

    $currentUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_default' => true,
    ]);

    expect($tableView->isDefaultForCurrentUser())->toBeTrue();
});

test('toggleFavoriteForCurrentUser correctly toggles preference based on current authenticated user config', function () {
    /* @var User $currentUser */
    $currentUser = auth()->user();

    /* @var User $otherUser */
    $otherUser = User::factory()->create();

    /* @var SavedTableView $tableView */
    $tableView = $otherUser->tableViews()->create([
        'name' => 'Public View',
        'model_type' => Order::class,
        'is_public' => true,
        'view_state' => new TableViewState,
    ]);

    $currentUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_favorite' => false,
    ]);

    expect($tableView->isFavoriteForCurrentUser())->toBeFalse();

    $tableView->toggleFavoriteForCurrentUser();

    expect($tableView->isFavoriteForCurrentUser())->toBeTrue();

    $tableView->toggleFavoriteForCurrentUser();

    expect($tableView->isFavoriteForCurrentUser())->toBeFalse();

    $otherUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_favorite' => true,
    ]);

    expect($tableView->isFavoriteForCurrentUser())->toBeFalse();
});

test('toggleDefaultForCurrentUser correctly toggles preference based on current authenticated user config', function () {
    /* @var User $currentUser */
    $currentUser = auth()->user();

    /* @var User $otherUser */
    $otherUser = User::factory()->create();

    /* @var SavedTableView $tableView */
    $tableView = $otherUser->tableViews()->create([
        'name' => 'Public View',
        'model_type' => Order::class,
        'is_public' => true,
        'view_state' => new TableViewState,
    ]);

    $currentUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_default' => false,
    ]);

    expect($tableView->isDefaultForCurrentUser())->toBeFalse();

    $tableView->toggleDefaultForCurrentUser();

    expect($tableView->isDefaultForCurrentUser())->toBeTrue();

    $tableView->toggleDefaultForCurrentUser();

    expect($tableView->isDefaultForCurrentUser())->toBeFalse();

    $otherUser->tableViewConfigs()->create([
        'saved_table_view_id' => $tableView->getKey(),
        'is_default' => true,
    ]);

    expect($tableView->isDefaultForCurrentUser())->toBeFalse();
});

test('only one saved table view user config can be default per user morph', function () {
    /* @var User $user */
    $user = auth()->user();

    $configs = collect();

    foreach (range(1, 5) as $i) {
        /* @var SavedTableView $tableView */
        $tableView = $user->tableViews()->create([
            'name' => "View {$i}",
            'model_type' => Order::class,
            'is_public' => true,
            'view_state' => new TableViewState,
        ]);

        $config = $user->tableViewConfigs()->create([
            'saved_table_view_id' => $tableView->getKey(),
            'is_default' => false,
            'is_favorite' => false,
        ]);

        $configs->push($config);
    }

    expect($user->tableViewConfigs())
        ->count()->toBe(5)
        ->where('is_default', true)->count()->toBe(0);

    $configs->skip(2)->first()->update(['is_default' => true]);
    $configs->skip(1)->first()->update(['is_default' => true]);
    $configs->first()->update(['is_default' => true]);

    expect($user->tableViewConfigs())
        ->where('is_default', true)->count()->toBe(1);
});
