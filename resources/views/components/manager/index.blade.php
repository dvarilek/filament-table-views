@php
    use Dvarilek\FilamentTableViews\Enums\TableViewTypeEnum;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'livewireId',
    'tableViews',
    'tableViewGroupOrder',
    'activeTableViewKey',
    'heading',
    'getGroupHeadingUsing',
    'getFilterLabelUsing',
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
    'isReorderable',
])

@php
    // TODO: Ensure the filter buttons get applied actually
    $hasTableViews = fn (TableViewTypeEnum $group) => filled($tableViews->get($group->value));
    $canRenderTableViewGroup = fn (TableViewTypeEnum $group) => $hasTableViews($group) && (! $hasFilterButtons || $activeFilters[$group->value]);

    $unfilteredTableViewGroups = collect(TableViewTypeEnum::cases())
       ->sortBy(
           $tableViewGroupOrder instanceof Closure ?
                $tableViewGroupOrder :
                fn (TableViewTypeEnum $group) => array_search($group, $tableViewGroupOrder, true)
       )
       ->values();

    $tableViewGroups = $unfilteredTableViewGroups->filter(fn(TableViewTypeEnum $group) => $tableViews->has($group->value));
@endphp

<div class="flex flex-1 flex-col">
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
                @foreach($unfilteredTableViewGroups as $group)
                    <x-filament::badge
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new Illuminate\View\ComponentAttributeBag([
                                    'size' => 'sm',
                                    'wire:loading.attr' => 'disabled',
                                    'wire:click' => 'toggleViewManagerFilterButton(\'' . $group->value . '\')',
                                    'disabled' => ! $hasTableViews($group),
                                    'color' => $canRenderTableViewGroup($group) ? 'primary' : 'gray',
                                    'icon' => $canRenderTableViewGroup($group) ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
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
    </div>

    @if ($canRenderTableViewGroup(TableViewTypeEnum::FAVORITE) || $canRenderTableViewGroup(TableViewTypeEnum::PRIVATE) || $canRenderTableViewGroup(TableViewTypeEnum::PUBLIC) || $canRenderTableViewGroup(TableViewTypeEnum::SYSTEM))
        <div
            class="space-y-6 overflow-y-auto px-6 pb-6"
            style="max-height: 500px"
            @if ($isCollapsible || $isReorderable)
                x-data="{
                    collapsedGroups: [],

                    reorderingGroup: null,

                    isReorderingDeferred: false,

                    pendingNewOrder: [],

                    toggleCollapsedGroup: function (group) {
                        if (this.isGroupCollapsed(group)) {
                            this.collapsedGroups.splice(this.collapsedGroups.indexOf(group), 1)

                            return
                        }

                        this.collapsedGroups.push(group)
                    },

                    isGroupCollapsed: function (group) {
                        return this.collapsedGroups.includes(group)
                    },

                    startReordering: function (group) {
                        this.reorderingGroup = group
                    },

                    stopReordering: function () {
                        this.reorderingGroup = null
                    },

                    isReordering: function (group) {
                        return this.reorderingGroup === group
                    },

                    handleGroupReorder: function (group, event) {
                        const newOrder = event.target.sortable.toArray()

                        if (this.isReorderingDeferred) {
                            this.pendingNewOrder = newOrder

                            return
                        }

                        $wire.reorderTableViewManagerTableViews(group, newOrder)
                    },
                }"
            @endif
        >
            @foreach ($tableViewGroups as $group)
                @if ($canRenderTableViewGroup($group))
                    <x-filament-table-views::manager.table-view-group
                        :group="$group"
                        :livewireId="$livewireId"
                        :groupHeading="$getGroupHeadingUsing($group)"
                        :tableViews="$tableViews->get($group->value, collect())"
                        :activeTableViewKey="$activeTableViewKey"
                        :actions="$group !== TableViewTypeEnum::SYSTEM ? $userTableViewActions : $systemTableViewActions"
                        :isCollapsible="$isCollapsible"
                        :isReorderable="$group !== TableViewTypeEnum::SYSTEM ? $isReorderable : false"
                    />
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
