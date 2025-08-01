<?php

namespace Dvarilek\FilamentTableViews\Tests\Tests\Fixtures;

use Dvarilek\FilamentTableViews\Components\TableView\TableView;
use Dvarilek\FilamentTableViews\Concerns\HasTableViews;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewManager;
use Dvarilek\FilamentTableViews\Tests\Models\Product;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LivewireTableViewFixture extends ParentLivewireTableViewFixture implements HasTableViewManager
{
    use HasTableViews;

    public static function getTableViewModelType(): string
    {
        return Product::class;
    }

    public function mount(): void
    {
        $this->toggledTableColumns = [
            'name' => false,
            'quantity' => false,
            'created_at' => true,
            'updated_at' => true,
        ];
    }

    public function getTableViews(): array
    {
        return [
            TableView::make('firstTableView')
                ->tableFilters([
                    'status' => [
                        'value' => 'active',
                    ],
                ])
                ->tableSort('quantity', 'asc')
                ->tableGrouping('created_at', 'asc')
                ->tableSearch('search 1')
                ->tableColumnSearches([
                    'name' => 'name search 1',
                    'total' => 'total search 1',
                ])
                ->hiddenTableColumns([
                    'name',
                    'quantity',
                ])
                ->visibleTableColumns([
                    'updated_at',
                ])
                ->activeTab('active'),
            TableView::make('secondTableView')
                ->tableFilters([
                    'status' => [
                        'value' => 'inactive',
                    ],
                ])
                ->tableSort('quantity', 'desc')
                ->tableGrouping('category', 'desc')
                ->tableSearch('search 2')
                ->tableColumnSearches([
                    'name' => 'name search 2',
                    'total' => 'total search 2',
                ])
                ->hiddenTableColumns([
                    'updated_at',
                ])
                ->visibleTableColumns([
                    'name',
                    'quantity',
                ])
                ->activeTab('inactive'),
            TableView::make('thirdTableView')
                ->toggledTableColumns([
                    'name' => true,
                    'quantity' => false,
                    'created_at' => false,
                    'updated_at' => false,
                ]),
        ];
    }

    public ?string $activeTab = null;

    public function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->query(Product::query())
            ->groups([
                Tables\Grouping\Group::make('status'),
            ])
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('quantity'),
                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'active',
                        'inactive' => 'inactive',
                    ]),
            ]);
    }

    public function render(): string
    {
        return '{{ $this->getTable() }}';
    }
}
