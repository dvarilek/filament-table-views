@php
    use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
    use Dvarilek\FilamentTableViews\Contracts\HasTableViewManager;
    use Illuminate\View\ComponentAttributeBag;

    /* @var HasTableViewManager $livewire */
    $livewire = $getLivewire();
    $livewireId = $livewire->getId();

    $heading = $getHeading();

    $isSearchable = $isSearchable();
    $searchDebounce = $getSearchDebounce();
    $isSearchOnBlur = $isSearchOnBlur();
    $searchLabel = $getSearchLabel();
    $searchPlaceholder = $getSearchPlaceholder();

    $resetLabel = $getResetLabel();
    $emptyStatePlaceholder = $getEmptyStatePlaceholder();

    $isFilterable = $isFilterable();
    $filters = $livewire->tableViewManagerActiveFilters;
    $isReorderable = $isReorderable();
    $isDeferredReorderable = $isDeferredReorderable();
    $isMultiGroupReorderable = $isMultiGroupReorderable();

    $isCollapsible = $isCollapsible();
    $defaultCollapsedGroups = array_map(fn (TableViewGroupEnum $group) => $group->value, $getDefaultCollapsedGroups());
    $tableViewGroups = $getTableViewGroups();

    $activeTableViewKey = $livewire->activeTableViewKey;
    $systemTableViewActions = $livewire->getTableViewManagerSystemActions();
    $userTableViewActions = $livewire->getTableViewManagerUserActions();

    $tableViews = $livewire->getAllTableViews(shouldGroupByTableViewType: true);
    $filteredTableViews = $livewire->filterTableViewManagerTableViews($tableViews);

    // TODO: Finish this, also consider passing some data through ->viewData and maybe move dropdown into here 
    $hasTableViewsVisible = fn (TableViewGroupEnum $group) => filled($filteredTableViews->get($group->value));
    $canRenderTableViewGroup = fn (TableViewGroupEnum $group) => $isMultiGroupReorderable || ($hasTableViewsVisible($group) && (! $isFilterable || $filters[$group->value]));

@endphp

<div
    class="flex flex-1 flex-col"
    @if ($isCollapsible || $isReorderable)
        x-data="{
            collapsedGroups: new Set(@js($defaultCollapsedGroups)),

            isDeferredReorderable: @js($isDeferredReorderable),

            pendingReorderedRecords: new Map(),

            isMultiGroupReorderable: @js($isMultiGroupReorderable),

            activeReorderingGroup: null,

            isTrackingChanges: true,

            isMultiGroupReorderingActive: false,

            isLoading: false,

            toggleCollapsedGroup(group) {
                if (this.isGroupCollapsed(group)) {
                    this.collapsedGroups.delete(group)

                    return
                }

                this.collapsedGroups.add(group)
            },

            isGroupCollapsed: function (group) {
                return this.collapsedGroups.has(group)
            },

            isReorderingActive(group = null) {
                if (this.isMultiGroupReorderable) {
                    return this.isMultiGroupReorderingActive;
                }

                return this.activeReorderingGroup === group;
            },

            startReordering(group = null) {
                if (this.isLoading) {
                    return
                }

                if (this.isMultiGroupReorderable) {
                    this.isMultiGroupReorderingActive = true;

                    return;
                }

                this.activeReorderingGroup = group;
            },

            stopReordering(group = null) {
                if (this.isMultiGroupReorderable) {
                    this.isMultiGroupReorderingActive = false;

                    return;
                }

                this.activeReorderingGroup = null;
            },

            hasPendingReorderedRecords(group = null) {
                if (group) {
                    return this.pendingReorderedRecords.has(group)
                }

                return this.pendingReorderedRecords.size > 0
            },

            async toggleReordering(group = null) {
                if (this.isLoading) {
                    return
                }

                if (this.isReorderingActive(group)) {
                    if (this.isDeferredReorderable) {
                        const pendingReorderedRecords = this.pendingReorderedRecords

                        if (pendingReorderedRecords.size > 0) {
                            if (this.isMultiGroupReorderable) {
                                await this.reorderMultipleGroups(pendingReorderedRecords)
                            } else {
                                await this.reorderGroup(group, pendingReorderedRecords.get(group))
                            }

                            this.pendingReorderedRecords.clear()
                        }
                    }

                    this.stopReordering(group)

                    return
                }

                this.startReordering(group)
            },


            async handleGroupReorder(event) {
                const newOrder = new Set(event.target.sortable.toArray())
                const group = event.target.dataset.tableViewGroup

                if (this.isDeferredReorderable) {
                    this.pendingReorderedRecords.set(group, newOrder)

                    return
                }

                await this.reorderGroup(group, newOrder)
            },

            async handleMultiGroupReorder(event) {
                const fromGroup = event.from.dataset.tableViewGroup
                const fromNewOrder = new Set(event.from.sortable.toArray())

                const toGroup = event.to.dataset.tableViewGroup
                const toNewOrder = new Set(event.to.sortable.toArray())

                if (this.isDeferredReorderable) {
                     this.pendingReorderedRecords.set(fromGroup, fromNewOrder)

                     if (fromGroup !== toGroup) {
                        this.pendingReorderedRecords.set(toGroup, toNewOrder)
                     }

                     return
                }

                await this.reorderMultipleGroups(new Map([
                    [fromGroup, fromNewOrder],
                    [toGroup, toNewOrder]
                ]))
            },

            async reorderGroup(group, order) {
                if (! order.size) {
                    return
                }

                this.isLoading = true

                try {
                    await $wire.reorderTableViewsInGroup(group, [...order])
                } finally {
                    this.isLoading = false
                }
            },

            async reorderMultipleGroups(groupOrdersMap) {
                if (groupOrdersMap.size === 1) {
                    await this.reorderGroup(...groupOrdersMap.entries().next().value)

                    return
                }

                this.isLoading = true

                try {
                    const groupedTableViewOrders = Object.fromEntries(
                      [...groupOrdersMap.entries()].map(([group, order]) => [group, [...order]])
                    )

                    await $wire.reorderTableViewsInGroups(groupedTableViewOrders)
                } finally {
                    this.isLoading = false
                }
            }
        }"
    @endif
>
    <div class="flex flex-1 flex-col space-y-4 px-6 pb-6 pt-6">
        <div class="flex justify-between">
            <h4
                class="text-base font-semibold leading-6 text-gray-950 dark:text-white"
            >
                {{ $heading }}
            </h4>

            @if ($isSearchable || $isFilterable)
                <div>
                    <x-filament::link
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new Illuminate\View\ComponentAttributeBag([
                                    'color' => 'danger',
                                    'tag' => 'button',
                                    'wire:click' => 'resetTableViewManager',
                                    'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                                    'wire:target' => 'resetTableViewManager',
                                ])
                            )
                        "
                    >
                        {{ $getResetLabel() }}
                    </x-filament::link>

                    <x-filament::loading-indicator
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new Illuminate\View\ComponentAttributeBag([
                                    'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                                    'wire:target' => 'resetTableViewManager',
                                ])
                            )
                                ->class(['h-5 w-5 text-gray-400 dark:text-gray-500'])
                        "
                    />
                </div>
            @endif
        </div>

        @if ($isSearchable)
            @php
                $searchWireModelAttribute = $isSearchOnBlur ? 'wire:model.blur' : "wire:model.live.debounce.{$searchDebounce}";
            @endphp

            <div x-id="['input']" class="pt-1">
                <label x-bind:for="$id('input')" class="sr-only">
                    {{ $searchLabel }}
                </label>

                <x-filament::input.wrapper
                    inline-prefix
                    prefix-icon="heroicon-m-magnifying-glass"
                    prefix-icon-alias="filament-table-views::manager-search-field"
                    wire:target="tableViewManagerSearch"
                >
                    <x-filament::input
                        :attributes="
                            (new ComponentAttributeBag)->merge([
                                'autocomplete' => 'off',
                                'inlinePrefix' => true,
                                'placeholder' => $searchPlaceholder,
                                'type' => 'search',
                                'wire:key' => 'filament-table-views-manager-search-field-' . $livewireId . 'tableViewManagerSearch',
                                $searchWireModelAttribute => 'tableViewManagerSearch',
                                'x-bind:id' => '$id(\'input\')',
                                'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                            ])
                        "
                    />
                </x-filament::input.wrapper>
            </div>
        @endif

        @if ($isFilterable)
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                @foreach($tableViewGroups as $group)
                    <x-filament::badge
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new Illuminate\View\ComponentAttributeBag([
                                    'size' => 'sm',
                                    'wire:loading.attr' => 'disabled',
                                    'wire:click' => 'toggleViewManagerFilterButton(\'' . $group->value . '\')',
                                    'disabled' => ! filled($tableViews->get($group->value)),
                                    'color' => $getFilterColor($group, $hasTableViewsVisible($group)),
                                    'icon' => $getFilterIcon($group, $hasTableViewsVisible($group)),
                                ])
                            )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                        "
                    >
                        {{ $getFilterLabel($group) }}

                        @if ($tableViewCount = ($tableViews->get($group->value)?->count()))
                            <span
                                class="pointer-events-none absolute -left-2 -top-1 h-4 w-4 select-none text-center"
                            >
                                {{ $tableViewCount > 99 ? '99+' : $tableViewCount }}
                            </span>
                        @endif
                    </x-filament::badge>
                @endforeach
            </div>
        @endif

        @if ($isReorderable && $isMultiGroupReorderable)
            <div class="flex flex-1 justify-between">
                <h5
                    class="text-sm font-medium uppercase tracking-wide "
                >
                    Multi group reordering
                </h5>

                <x-filament-table-views::manager.reordering-indicator
                    :isDeferredReorderable="$isDeferredReorderable"
                    :isMultiGroupReorderable="$isMultiGroupReorderable"
                />
            </div>
        @endif
    </div>

    @if ($canRenderTableViewGroup(TableViewGroupEnum::FAVORITE) || $canRenderTableViewGroup(TableViewGroupEnum::PRIVATE) || $canRenderTableViewGroup(TableViewGroupEnum::PUBLIC) || $canRenderTableViewGroup(TableViewGroupEnum::SYSTEM))
        <div
            class="space-y-6 overflow-y-auto px-6 pb-6"
            style="max-height: 500px"
        >
            @foreach ($tableViewGroups as $group)
                @if ($canRenderTableViewGroup($group))
                    @php
                        $groupValue = $group->value;

                        $canCollapseGroup = $isCollapsible && $isGroupCollapsible($group);
                        $canReorderGroup = $isReorderable && $isGroupReorderable($group) && $group !== TableViewGroupEnum::SYSTEM; // temp
                        $tableViewActions = $group !== TableViewGroupEnum::SYSTEM ? $userTableViewActions : $systemTableViewActions
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div
                                @class([
                                    'cursor-pointer' => $canCollapseGroup,
                                    'flex items-center gap-x-2',
                                ])
                                @if ($canCollapseGroup)
                                    x-on:click="toggleCollapsedGroup(@js($groupValue))"
                                @endif
                            >
                                @if ($groupHeading = $getGroupHeading($group))
                                    <h5
                                        class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $groupHeading }}
                                    </h5>
                                @endif

                                @if ($canCollapseGroup)
                                    <x-filament::icon
                                        icon="heroicon-o-chevron-up"
                                        class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                        x-bind:class="isGroupCollapsed('{{ $groupValue }}') && '-rotate-180'"
                                    />
                                @endif
                            </div>


                            @if ($canReorderGroup && ! $isMultiGroupReorderable)
                                <div class="px-2">
                                    <x-filament-table-views::manager.reordering-indicator
                                        :groupValue="$groupValue"
                                        :isCollapsible="$canCollapseGroup"
                                        :isDeferredReorderable="$isDeferredReorderable"
                                        :isMultiGroupReorderable="$isMultiGroupReorderable"
                                    />
                                </div>
                            @endif
                        </div>

                        <div
                            class="space-y-1"
                            @if ($isGroupCollapsible($group))
                                x-show="! isGroupCollapsed(@js($groupValue))"
                                x-bind:aria-expanded="! isGroupCollapsed(@js($groupValue))"
                                aria-expanded="true"
                            @endif
                            @if ($canReorderGroup)
                                x-sortable
                                x-sortable-animation="400s"
                                data-table-view-group="{{ $groupValue }}"
                                @if ($isMultiGroupReorderable)
                                    x-sortable-group="shared"
                                    x-on:end.stop="handleMultiGroupReorder($event)"
                                @else
                                    x-on:end.stop="handleGroupReorder($event)"
                                @endif
                            @endif
                        >
                            @foreach($filteredTableViews->get($group->value, collect()) as $key => $tableView)
                                <x-filament-table-views::manager.table-view-group-item
                                    :wire-key="'filament-table-views-manager-' . $groupValue . '-view-' . $key . '-' . $livewireId"
                                    :key="$key"
                                    :group="$group"
                                    :tableView="$tableView"
                                    :activeTableViewKey="$activeTableViewKey"
                                    :actions="$tableViewActions"
                                    :isReorderable="$canReorderGroup && $isRecordReorderable($tableView->getRecord())"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="mx-auto grid max-w-lg justify-items-center text-center">
            <div class="my-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                <x-filament::icon
                    icon="heroicon-m-x-mark"
                    class="h-6 w-6 text-gray-500 dark:text-gray-400"
                />
            </div>

            @if ($emptyStatePlaceholder)
                <h4 class="text-md pb-6 font-normal leading-6">
                    {{ $emptyStatePlaceholder }}
                </h4>
            @endif
        </div>
    @endif
</div>
