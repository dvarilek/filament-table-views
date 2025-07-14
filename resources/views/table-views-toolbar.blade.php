@if(in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    @php
        $defaultTableViews = $this->getDefaultTableViews();
        $customTableViews = $this->getCustomTableViews();

        [$publicCustomTableViews, $personalCustomTableViews] = collect($customTableViews)
            ->partition(static fn (\Dvarilek\FilamentTableViews\Components\Table\TableView $tableView) => $tableView->isPublic())
            ->toArray();

        $livewireId = $this->getId();
        $activeTableViewKey = $this->activeTableViewKey;
    @endphp

    @if (filled($defaultTableViews) || filled($publicCustomTableViews) || filled($personalCustomTableViews))
        <div
            class='px-4 -mb-6 flex flex-1 items-center justify-between gap-x-4'
        >
            <nav
                class="fi-table-views-toolbar flex items-center gap-x-2 overflow-x-auto"
            >
                @foreach($defaultTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-default-view-' . $key . '-' . $livewireId"
                        :tableView="$tableView"
                        :key="$key"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if (filled($defaultTableViews) && filled($publicCustomTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($publicCustomTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-public-custom-view-' . $key . '-' . $livewireId"
                        :tableView="$tableView"
                        :key="$key"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if (filled($publicCustomTableViews) && filled($personalCustomTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($personalCustomTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-personal-custom-view-' . $key . '-' . $livewireId"
                        :tableView="$tableView"
                        :key="$key"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach
            </nav>

            <div class="flex gap-x-4 items-center">
                {{ $this->createTableViewAction }}

                <x-filament::dropdown
                    placement="bottom-start"
                    :width="\Filament\Support\Enums\MaxWidth::Small"
                >
                    <x-slot name="trigger">
                        {{ $this->manageTableViewsAction }}
                    </x-slot>

                    <x-filament-table-views::manager
                        :livewireId="$livewireId"
                        :customTableViews="$this->filterTableViewManagerItems($customTableViews)"
                        :defaultTableViews="$defaultTableViews"
                        :customTableViewActions="$this->getCustomTableViewActions()"
                        :activeTableViewKey="$activeTableViewKey"
                        :tableViewManagerSearch="$this->tableViewManagerSearch"
                        :tableViewManagerSearchDebounce="$this->getTableViewManagerSearchDebounce()"
                        :tableViewManagerSearchOnBlur="$this->getTableViewManagerSearchOnBlur()"
                        :tableViewManagerSearchLabel="$this->getTableViewManagerSearchLabel()"
                        :tableViewManagerSearchPlaceholder="$this->getTableViewManagerSearchPlaceholder()"
                        :tableViewManagerActiveFilters="$this->tableViewManagerActiveFilters"
                        :tableViewManagerActions="$this->getTableViewManagerActions()"
                    />
                </x-filament::dropdown>
            </div>
        </div>
    @endif
@endif
