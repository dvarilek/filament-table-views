@if (! in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    <span></span>
@else
    <div class="fi-table-views-toolbar flex flex-1 items-center gap-x-3 overflow-x-auto">
        @foreach($this->getTableViews() as $key => $tableView)
            <x-filament-table-views::table-view
                :wire-key="$key"
                :tableView="$tableView"
                :key="$key"
            />
        @endforeach

        <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>

        @foreach($this->getCustomTableViews() as $key => $customTableView)
            <x-filament-table-views::table-view
                :wire-key="$key"
                :tableView="$customTableView"
                :key="$key"
            />
        @endforeach
    </div>
@endif
