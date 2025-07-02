<?php

namespace Dvarilek\FilamentTableViews\Tests\Tests\Fixtures;

use Dvarilek\FilamentTableViews\Tests\Models\Order;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Component;

class TestLivewire extends Component implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    public function mount()
    {
        $this->tableFilters = [
            'trashed' => ['value' => '1'],
            'queryBuilder' => ['rules' => []],
            'client' => ['values' => ['1', '5', '8']],
            'created_at' => [
                'created_from' => '2025-06-30',
                'created_until' => '2025-08-10',
            ],
        ];

        $this->tableGrouping = 'created_at';
        $this->tableGroupingDirection = 'desc';
        $this->tableSearch = 'fw';
        $this->tableSortColumn = 'currency';
        $this->tableSortDirection = 'asc';
        $this->activeTab = 'processing';
    }

    public ?string $activeTab = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->groups([
                Tables\Grouping\Group::make('created_at'),
            ]);
    }

    public function render(): string
    {
        return '{{ $this->getTable() }}';
    }
}
