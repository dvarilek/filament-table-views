@if(in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    @php
        [$defaultTableViews, $customTableViews] = [$this->getDefaultTableViews(), $this->getCustomTableViews()];

        $livewireId = $this->getId();
        $tableViewIconPosition = $this->getTableViewIconPosition();
        $activeTableViewKey = $this->activeTableViewKey;
    @endphp

    @if (filled($defaultTableViews) || filled($customTableViews))
        <div
            class='px-2 -mb-6 flex flex-1 items-center'
        >
            <nav
                class="fi-table-views-toolbar flex flex-1 items-center gap-x-2 overflow-x-auto min-w-0"
            >
                @foreach($defaultTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-default-view-' . $key . '-' . $livewireId"
                        :tableView="$tableView"
                        :key="$key"
                        :iconPosition="$tableViewIconPosition"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if (filled($defaultTableViews) && filled($customTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($customTableViews as $key => $customTableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-custom-view-' . $key . '-' . $livewireId"
                        :tableView="$customTableView"
                        :key="$key"
                        :iconPosition="$tableViewIconPosition"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach
            </nav>
            <div class="flex gap-x-4">
                {{ $this->createTableViewAction }}

                <x-filament::dropdown
                    placement="bottom-start"
                    :width="\Filament\Support\Enums\MaxWidth::ExtraSmall"
                >
                    <x-slot name="trigger">
                        {{ $this->manageTableViewsAction }}
                    </x-slot>

                    <x-filament-table-views::manager
                        :customTableViews="$customTableViews"
                        :defaultTableViews="$defaultTableViews"
                        :customTableViewActions="$this->getCustomTableViewActions()"
                        :activeTableViewKey="$activeTableViewKey"
                    />
                </x-filament::dropdown>
            </div>
        </div>
    @endif
@endif
