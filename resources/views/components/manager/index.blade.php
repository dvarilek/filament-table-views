@php
    use Dvarilek\FilamentTableViews\Enums\TableViewGroupEnum;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'livewireId',
    'tableViews',
    'filterTableViewsUsing',
    'tableViewGroupOrder',
    'activeTableViewKey',
    'heading',
    'getGroupHeadingUsing',
    'getFilterLabelUsing',
    'getFilterColorUsing',
    'getFilterIconUsing',
    'isSearchable',
    'searchDebounce' => '500ms',
    'searchOnBlur' => false,
    'searchLabel',
    'searchPlaceholder',
    'emptyStatePlaceholder',
    'hasFilterButtons',
    'activeFilters',
    'resetLabel',
    'systemTableViewActions',
    'userTableViewActions',
    'isCollapsible',
    'defaultCollapsedGroups',
    'isReorderable',
    'isDeferredReorderable',
    'isMultiGroupReorderable'
])

@php
    $filteredTableViews = $filterTableViewsUsing($tableViews);
    $hasTableViews = fn (TableViewGroupEnum $group) => filled($tableViews->get($group->value));

    $hasTableViewsVisible = fn (TableViewGroupEnum $group) => filled($filteredTableViews->get($group->value));

    // TODO: This is quite messy - refactor this check, I would argue that table groups and it breaks with filters....
    $canRenderTableViewGroup = fn (TableViewGroupEnum $group) => $isMultiGroupReorderable || ($hasTableViews($group) && (! $hasFilterButtons || $activeFilters[$group->value]));

    $tableViewGroups = collect(TableViewGroupEnum::cases())
       ->sortBy(
           $tableViewGroupOrder instanceof Closure ?
                $tableViewGroupOrder :
                fn (TableViewGroupEnum $group) => array_search($group, $tableViewGroupOrder, true)
       )
       ->values();

    $filteredTableViewGroups = $tableViewGroups->filter(fn(TableViewGroupEnum $group) => $filteredTableViews->has($group->value));

    $defaultCollapsedGroups = array_map(fn (TableViewGroupEnum $group) => $group->value, $defaultCollapsedGroups);

    // TODO: Rename pendingReorderingOrder to pendingReorderedRecords - also do for that singular variant, maybe remove the original - might be tricky to do
@endphp

<div
    class="flex flex-1 flex-col"
    @if ($isCollapsible || $isReorderable)
       x-data="{
            collapsedGroups: new Set(@js($defaultCollapsedGroups)),

            isDeferredReorderable: @js($isDeferredReorderable),

            isMultiGroupReorderable: @js($isMultiGroupReorderable),

            activeReorderingGroup: null,

            pendingReorderingOrder: new Set(),

            isMultiGroupReorderingActive: false,

            pendingReorderingOrders: new Map(),

            isTrackingOrderChanges: @js($isDeferredReorderable),

            reorderedRecords: new Set(),

            isLoading: false,

            toggleCollapsedGroup: function (group) {
                if (this.isGroupCollapsed(group)) {
                    this.collapsedGroups.delete(group)

                    return
                }

                this.collapsedGroups.add(group)
            },

            isGroupCollapsed: function (group) {
                return this.collapsedGroups.has(group)
            },

            startGroupReordering: function (group) {
                if (this.isLoading) {
                    return
                }

                this.activeReorderingGroup = group
            },

            stopGroupReordering: function () {
                this.activeReorderingGroup = null
            },

            isGroupReordering: function (group) {
                return this.activeReorderingGroup === group
            },

            toggleGroupReordering: async function (group) {
                if (this.isLoading) {
                    return
                }

                if (this.isGroupReordering(group)) {
                    if (this.isDeferredReorderable) {
                        await this.reorderGroup(group, this.pendingReorderingOrder)

                        this.pendingReorderingOrder = new Set()

                        if (this.isTrackingOrderChanges) {
                            this.reorderedRecords = new Set()
                        }
                    }

                    this.stopGroupReordering(group)

                    return
                }

                this.startGroupReordering(group)
            },

            handleGroupReorder: async function (event) {
                const newOrder = new Set(event.target.sortable.toArray())
                const group = event.target.dataset.tableViewGroup

                if (this.isDeferredReorderable) {
                    this.pendingReorderingOrder = newOrder

                    return
                }

                await this.reorderGroup(group, newOrder)
            },

            reorderGroup: async function (group, order) {
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

            startMultiGroupReordering: function () {
                if (this.isLoading) {
                    return
                }

                this.isMultiGroupReorderingActive = true
            },

            stopMultiGroupReordering: function () {
                this.isMultiGroupReorderingActive = false
            },

            isMultiGroupReordering: function () {
                return this.isMultiGroupReorderingActive
            },

            toggleMultiGroupReordering: async function () {
                if (this.isLoading) {
                    return
                }

                if (this.isMultiGroupReordering()) {
                    if (this.isDeferredReorderable) {
                        await this.reorderGroups(this.pendingReorderingOrders)

                        this.pendingReorderingOrders.clear()
                    }

                    this.stopMultiGroupReordering()

                    return
                }

                this.startMultiGroupReordering()
            },

            handleMultiGroupReorder: async function (event) {
                const fromGroup = event.from.dataset.tableViewGroup
                const fromNewOrder = new Set(event.from.sortable.toArray())

                const toGroup = event.to.dataset.tableViewGroup
                const toNewOrder = new Set(event.to.sortable.toArray())

                if (this.isDeferredReorderable) {
                     this.pendingReorderingOrders.set(fromGroup, fromNewOrder)
                     this.pendingReorderingOrders.set(toGroup, toNewOrder)

                     return
                }

                await this.reorderGroups(new Map([
                    [fromGroup, fromNewOrder],
                    [toGroup, toNewOrder]
                ]))
            },

            reorderGroups: async function (groupOrdersMap) {
                if (groupOrdersMap.size === 1) {
                    await this.reorderGroup(...groupOrdersMap.entries().next().value)

                    return
                }

                this.isLoading = true

                try {
                    const groupedTableViewOrders = Object.fromEntries(
                      [...this.pendingReorderingOrders.entries()].map(([group, order]) => [group, [...order]])
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

            @if ($isSearchable || $hasFilterButtons)
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
                        {{ $resetLabel }}
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
                $searchWireModelAttribute = $searchOnBlur ? 'wire:model.blur' : "wire:model.live.debounce.{$searchDebounce}";
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

        @if ($hasFilterButtons)
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                @foreach($tableViewGroups as $group)
                    <x-filament::badge
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new Illuminate\View\ComponentAttributeBag([
                                    'size' => 'sm',
                                    'wire:loading.attr' => 'disabled',
                                    'wire:click' => 'toggleViewManagerFilterButton(\'' . $group->value . '\')',
                                    'disabled' => ! $hasTableViews($group),
                                    'color' => $getFilterColorUsing($group, $canRenderTableViewGroup($group)),
                                    'icon' => $getFilterIconUsing($group, $canRenderTableViewGroup($group)),
                                ])
                            )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                        "
                    >
                        {{ $getFilterLabelUsing($group) }}

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
                    :isCollapsible="$isCollapsible"
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
                        $isReorderable = $group !== TableViewGroupEnum::SYSTEM ? $isReorderable : false;
                        $tableViewActions = $group !== TableViewGroupEnum::SYSTEM ? $userTableViewActions : $systemTableViewActions
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div
                                @class([
                                    'cursor-pointer' => $isCollapsible,
                                    'flex items-center gap-x-2',
                                ])
                                @if ($isCollapsible)
                                    x-on:click="toggleCollapsedGroup(@js($groupValue))"
                                @endif
                            >
                                @if ($groupHeading = $getGroupHeadingUsing($group))
                                    <h5
                                        class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $groupHeading }}
                                    </h5>
                                @endif

                                @if ($isCollapsible)
                                    <x-filament::icon
                                        icon="heroicon-o-chevron-up"
                                        class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                        x-bind:class="isGroupCollapsed('{{ $groupValue }}') && '-rotate-180'"
                                    />
                                @endif
                            </div>

                            @if ($isReorderable && ! $isMultiGroupReorderable)
                                <x-filament-table-views::manager.reordering-indicator
                                    :groupValue="$groupValue"
                                    :isCollapsible="$isCollapsible"
                                    :isDeferredReorderable="$isDeferredReorderable"
                                    :isMultiGroupReorderable="$isMultiGroupReorderable"
                                />
                            @endif
                        </div>

                        <div
                            class="space-y-1"
                            @if ($isCollapsible)
                                x-show="! isGroupCollapsed(@js($groupValue))"
                                x-bind:aria-expanded="! isGroupCollapsed(@js($groupValue))"
                                aria-expanded="true"
                            @endif
                            @if ($isReorderable)
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
                                    :isReorderable="$isReorderable"
                                    :isMultiGroupReorderable="$isMultiGroupReorderable"
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
