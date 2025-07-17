@if(in_array(\Dvarilek\FilamentTableViews\Concerns\HasTableViews::class, class_uses_recursive($this)))
    @php
        $livewireId = $this->getId();

        $defaultTableViews = $this->getDefaultTableViews();
        $userTableViews = $this->getUserTableViews;

        [$favoriteUserTableViews, $nonFavoriteUserTableViews] = collect($userTableViews)
            //->filter(static fn (\Dvarilek\FilamentTableViews\Components\UserView $tableView) => $tableView->isVisible())
            ->partition(static fn (\Dvarilek\FilamentTableViews\Components\UserView $tableView) => $tableView->isFavorite())
            ->toArray();

        [$publicUserTableViews, $privateUserTableViews] = collect($nonFavoriteUserTableViews)
            ->partition(static fn (\Dvarilek\FilamentTableViews\Components\UserView $tableView) => $tableView->isPublic())
            ->toArray();

        $hasDefaultTableViews = filled($defaultTableViews);
        $hasFavoriteUserTableViews = filled($favoriteUserTableViews);

        $activeTableViewKey = $this->activeTableViewKey;
        $activeTableView = $this->getActiveTableView();
    @endphp

    <div class='px-4 sm:px-6 -mb-6 flex flex-1 items-center justify-between gap-x-4'>
        @if ($hasDefaultTableViews || $hasFavoriteUserTableViews)
            <nav class="fi-table-views-toolbar flex items-center gap-x-2 overflow-x-auto">

                {{-- Consider if this makes any sense--}}
                @if ($activeTableView instanceof \Dvarilek\FilamentTableViews\Components\UserView && ! $activeTableView->isFavorite())
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-active-view-' . $activeTableViewKey . '-' . $livewireId"
                        :key="$activeTableViewKey"
                        :tableView="$activeTableView"
                        :isActive="true"
                    />

                    @if ($hasDefaultTableViews || $hasFavoriteUserTableViews)
                        <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                    @endif
                @endif

                @foreach($defaultTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-default-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach

                @if ($hasDefaultTableViews && $hasFavoriteUserTableViews)
                    <span class="border-e h-6 border-gray-300 dark:border-gray-700"></span>
                @endif

                @foreach($favoriteUserTableViews as $key => $tableView)
                    <x-filament-table-views::table-view
                        :wire:key="'filament-table-views-toolbar-favorite-view-' . $key . '-' . $livewireId"
                        :key="$key"
                        :tableView="$tableView"
                        :isActive="$activeTableViewKey === (string) $key"
                    />
                @endforeach
            </nav>
        @endif

        <div class="flex gap-x-4 items-center">
            {{ $this->createTableViewAction }}

            <x-filament::dropdown
                placement="bottom-start"
                :width="$this->getTableViewManagerWidth()"
            >
                <x-slot name="trigger">
                    {{ $this->manageTableViewsAction }}
                </x-slot>

                <x-filament-table-views::manager
                    :livewireId="$livewireId"
                    :defaultTableViews="$this->filterTableViewManagerItems($defaultTableViews)"
                    :favoriteUserTableViews="$this->filterTableViewManagerItems($favoriteUserTableViews)"
                    :privateUserTableViews="$this->filterTableViewManagerItems($privateUserTableViews)"
                    :publicUserTableViews="$this->filterTableViewManagerItems($publicUserTableViews)"
                    :activeTableViewKey="$activeTableViewKey"
                    :heading="$this->getTableViewmanagerHeading()"
                    :favoriteSectionHeading="$this->getTableViewManagerFavoriteSectionHeading()"
                    :privateSectionHeading="$this->getTableViewManagerPrivateSectionHeading()"
                    :publicSectionHeading="$this->getTableViewManagerPublicSectionHeading()"
                    :defaultSectionHeading="$this->getTableViewManagerDefaultSectionHeading()"
                    :isSearchable="$this->hasTableViewManagerSearch()"
                    :searchDebounce="$this->getTableViewManagerSearchDebounce()"
                    :searchOnBlur="$this->getTableViewManagerSearchOnBlur()"
                    :searchLabel="$this->getTableViewManagerSearchLabel()"
                    :searchPlaceholder="$this->getTableViewManagerSearchPlaceholder()"
                    :emptyStatePlaceholder="$this->getTableViewManagerEmptyStatePlaceholder()"
                    :hasFilterButtons="$this->hasTableViewManagerFilterButtons()"
                    :activeFilters="$this->tableViewManagerActiveFilters"
                    :favoriteFilterLabel="$this->getTableViewManagerFavoriteFilterLabel()"
                    :privateFilterLabel="$this->getTableViewManagerPrivateFilterLabel()"
                    :publicFilterLabel="$this->getTableViewManagerPublicFilterLabel()"
                    :defaultFilterLabel="$this->getTableViewManagerDefaultFilterLabel()"
                    :resetLabel="$this->getTableViewManagerResetLabel()"
                    :defaultActions="$this->getTableViewManagerDefaultActions()"
                    :userActions="$this->getTableViewManagerUserActions()"
                    :isCollapsible="$this->hasTableViewManagerCollapsibleGroups()"
                />
            </x-filament::dropdown>
        </div>
    </div>
@endif
