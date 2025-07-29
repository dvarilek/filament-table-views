@php
    use Dvarilek\FilamentTableViews\Components\TableView\TableView;
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Dvarilek\FilamentTableViews\Concerns\HasTableViews;
    use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
    use Illuminate\Support\Collection;
@endphp

@if (in_array(HasTableViews::class, class_uses_recursive($this)))
    @php
        $livewireId = $this->getId();

        $tableViews = $this->getAllTableViews(shouldGroupByTableViewType: true);

        $systemTableViews = $tableViews->get(TableViewGroupEnum::SYSTEM->value, collect());
        $favoriteUserTableViews = $tableViews->get(TableViewGroupEnum::FAVORITE->value, collect());
        $publicUserTableViews = $tableViews->get(TableViewGroupEnum::PUBLIC->value, collect());
        $privateUserTableViews = $tableViews->get(TableViewGroupEnum::PRIVATE->value, collect());

        $hasSystemTableViews = filled($systemTableViews);
        $hasFavoriteUserTableViews = filled($favoriteUserTableViews);

        $activeTableViewKey = $this->activeTableViewKey;
        $activeTableView = $this->getActiveTableView();

        $tableViewManager = $this->getTableViewManager();
    @endphp

    <div
        class="-mb-6 flex flex-1 items-center justify-between gap-x-4 px-4 sm:px-6"
    >
        @if ($hasSystemTableViews || $hasFavoriteUserTableViews)
            <nav
                class="fi-table-views-toolbar flex items-center gap-x-2 overflow-x-auto"
            >
                {{-- Consider if this makes any sense --}}
                @if ($activeTableView instanceof UserView && ! $activeTableView->isFavorite())
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-active-view-' . $activeTableViewKey . '-' . $livewireId"
                        :key="$activeTableViewKey"
                        :tableView="$activeTableView"
                        :isActive="true"
                    />

                    @if ($hasSystemTableViews || $hasFavoriteUserTableViews)
                        <span
                            class="h-6 border-e border-gray-300 dark:border-gray-700"
                        ></span>
                    @endif
                @endif

                @foreach ($systemTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-system-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if ($hasSystemTableViews && $hasFavoriteUserTableViews)
                    <span
                        class="h-6 border-e border-gray-300 dark:border-gray-700"
                    ></span>
                @endif

                @foreach ($favoriteUserTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-favorite-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach
            </nav>
        @endif

        <div class="flex items-center gap-x-4">
            {{ $this->createTableViewAction }}

            <x-filament::dropdown
                placement="bottom-start"
                :width="$tableViewManager->getWidth()"
            >
                <x-slot name="trigger">
                    {{ $this->manageTableViewsAction }}
                </x-slot>

                {{ $tableViewManager }}
            </x-filament::dropdown>
        </div>
    </div>
@endif
