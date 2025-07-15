@if(in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    @php
        $livewireId = $this->getId();
        $activeTableViewKey = $this->activeTableViewKey;

        $defaultTableViews = $this->getDefaultTableViews();
        $userTableViews = $this->getUserTableViews();

        [$publicUserTableViews, $personalUserTableViews] = collect($userTableViews)
            ->partition(static fn (\Dvarilek\FilamentTableViews\Components\UserView $tableView) => $tableView->isPublic())
            ->toArray();
    @endphp

    @if (filled($defaultTableViews) || filled($publicUserTableViews) || filled($personalUserTableViews))
        <div class='px-4 -mb-6 flex flex-1 items-center justify-between gap-x-4'>
            <nav class="fi-table-views-toolbar flex items-center gap-x-2 overflow-x-auto">
                @foreach($defaultTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-default-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if (filled($defaultTableViews) && filled($publicUserTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($publicUserTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-public-user-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if (filled($publicUserTableViews) && filled($personalUserTableViews))
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($personalUserTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire-key="'filament-table-views-toolbar-personal-user-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
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
                            :defaultTableViews="$this->filterTableViewManagerItems($defaultTableViews)"
                            :userTableViews="$this->filterTableViewManagerItems($userTableViews)"
                            :activeTableViewKey="$activeTableViewKey"
                            :tableViewManagerSearch="$this->tableViewManagerSearch"
                            :tableViewManagerSearchDebounce="$this->getTableViewManagerSearchDebounce()"
                            :tableViewManagerSearchOnBlur="$this->getTableViewManagerSearchOnBlur()"
                            :tableViewManagerSearchLabel="$this->getTableViewManagerSearchLabel()"
                            :tableViewManagerSearchPlaceholder="$this->getTableViewManagerSearchPlaceholder()"
                            :tableViewManagerActiveFilters="$this->tableViewManagerActiveFilters"
                            :tableViewManagerDefaultActions="$this->getTableViewManagerDefaultActions()"
                            :tableViewManagerUserActions="$this->getTableViewManagerUserActions()"
                    />
                </x-filament::dropdown>
            </div>
        </div>
    @endif
@endif
