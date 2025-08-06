@php
    use Dvarilek\FilamentTableViews\Components\TableView\TableView;
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Dvarilek\FilamentTableViews\Concerns\HasTableViews;
    use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
    use Illuminate\Support\Collection;
@endphp

@if (in_array(HasTableViews::class, class_uses_recursive($this)))
    @php
        $activeTableViewKey = $this->activeTableViewKey;
        $livewireId = $this->getId();

        $tableViews = $this->getAllTableViews(shouldGroupByTableViewType: true);

        $systemTableViews = $tableViews->get(TableViewGroupEnum::SYSTEM->value, collect());
        $hasSystemTableViews = filled($systemTableViews);

        $favoriteUserTableViews = $tableViews->get(TableViewGroupEnum::FAVORITE->value, collect());
        $hasFavoriteUserTableViews = filled($favoriteUserTableViews);

        $tableViewManager = $this->getTableViewManager();
        $createAction = $tableViewManager->getCreateAction();
        $manageAction = $tableViewManager->getManageAction();

        $canRenderCreateAction = $createAction && ! $createAction->isDisabled();
        $canRenderTableViewManager = $manageAction && ! $manageAction->isDisabled() && ! $tableViewManager->isDisabled();
    @endphp

    <div
        class="-mb-6 flex flex-1 items-center justify-between gap-x-4 px-4 sm:px-6"
    >
        @if ($hasSystemTableViews || $hasFavoriteUserTableViews)
            <nav
                class="fi-table-views-toolbar flex items-center gap-x-2 overflow-x-auto"
            >
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

        @if ($canRenderCreateAction || $canRenderTableViewManager)
            <div class="flex items-center gap-x-4">
                @if ($canRenderCreateAction)
                    {{ $createAction }}
                @endif

                @if ($canRenderTableViewManager)
                    {{ $tableViewManager }}
               @endif
            </div>
        @endif
    </div>
@endif
