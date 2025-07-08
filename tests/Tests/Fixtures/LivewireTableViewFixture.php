<?php

namespace Dvarilek\FilamentTableViews\Tests\Tests\Fixtures;

use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Concerns\HasTableViews;
use Dvarilek\FilamentTableViews\Tests\Models\Product;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class LivewireTableViewFixture extends Component implements HasForms, Tables\Contracts\HasTable
{
    use HasTableViews, Tables\Concerns\InteractsWithTable {
        Tables\Concerns\InteractsWithTable::filterTableQuery insteadof HasTableViews;
        Tables\Concerns\InteractsWithTable::filterTableQuery as baseFilterTableQuery;
    }
    use InteractsWithForms;

    public function filterTableQuery(Builder $query): Builder
    {
        $this->applyActiveTableViewToTableQuery($query);

        return $this->baseFilterTableQuery($query);
    }

    public static function getTableViewModelType(): string
    {
        return Product::class;
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
        $this->forget

        return $table
            ->query(Product::query())
            ->groups([
                Tables\Grouping\Group::make('status'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true),
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
