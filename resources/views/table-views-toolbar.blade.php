@if(in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    @php
        [$defaultTableViews, $customTableViews] = [$this->getDefaultTableViews(), $this->getCustomTableViews()];

        $livewireId = $this->getId();
        $tableViewIconPosition = $this->getTableViewIconPosition();
        $activeTableViewKey = $this->activeTableViewKey;
    @endphp

    @if (filled($defaultTableViews) || filled($customTableViews))
        <nav
            class="fi-table-views-toolbar flex flex-1 items-center gap-x-2 overflow-x-auto px-2 -mb-6"
        >
            <div
                class="flex flex-1 items-center gap-x-2 overflow-x-auto"
            >
                @foreach($defaultTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-default-view-' . $key . '-' . $livewireId"
                        :tableView="$tableView"
                        :key="$key"
                        :iconPosition="$tableViewIconPosition"
                        :isActive="$activeTableViewKey === $key"
                    />
                @endforeach

                @if (filled($defaultTableViews) && filled($customTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach(($customTableViews) as $key => $customTableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-custom-view-' . $key . '-' . $livewireId"
                        :tableView="$customTableView"
                        :key="$key"
                        :iconPosition="$tableViewIconPosition"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach
            </div>

            {{ $this->createTableViewAction }}
        </nav>
    @endif
@endif
