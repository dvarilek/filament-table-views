@php
    use Filament\Support\Enums\MaxWidth;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'livewireId',
    'systemTableViews',
    'favoriteUserTableViews',
    'privateUserTableViews',
    'publicUserTableViews',
    'activeTableViewKey',
    'heading',
    'favoriteSectionHeading',
    'privateSectionHeading',
    'publicSectionHeading',
    'systemSectionHeading',
    'isSearchable',
    'searchDebounce' => '500ms',
    'searchOnBlur' => false,
    'searchLabel',
    'searchPlaceholder',
    'emptyStatePlaceholder',
    'hasFilterButtons',
    'activeFilters',
    'favoriteFilterLabel',
    'privateFilterLabel',
    'publicFilterLabel',
    'systemFilterLabel',
    'resetLabel',
    'systemTableViewActions',
    'userTableViewActions',
    'isCollapsible',
    'isReorderable',
])

@php
    $hasSystemTableViews = filled($systemTableViews);
    $canRenderSystemTableViews = $hasSystemTableViews && (! $hasFilterButtons || $activeFilters['system']);

    $hasFavoriteUserTableViews = filled($favoriteUserTableViews);
    $canRenderFavoriteUserTableViews = $hasFavoriteUserTableViews && (! $hasFilterButtons || $activeFilters['favorite']);

    $hasPublicUserTableViews = filled($publicUserTableViews);
    $canRenderPublicUserTableViews = $hasPublicUserTableViews && (! $hasFilterButtons || $activeFilters['public']);

    $hasPrivateUserTableViews = filled($privateUserTableViews);
    $canRenderPrivateUserTableViews = $hasPrivateUserTableViews && (! $hasFilterButtons || $activeFilters['private']);
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
                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'favorite\')',
                                'disabled' => ! $hasFavoriteUserTableViews,
                                'color' => $canRenderFavoriteUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderFavoriteUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                    "
                >
                    {{ $favoriteFilterLabel }}

                    @if ($favoriteUserTableViewsCount = count($favoriteUserTableViews))
                        <span
                            class="pointer-events-none absolute -left-2 -top-1 h-4 w-4 select-none text-center"
                        >
                            {{ $favoriteUserTableViewsCount > 99 ? '99+' : $favoriteUserTableViewsCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'private\')',
                                'disabled' => ! $hasPrivateUserTableViews,
                                'color' => $canRenderPrivateUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderPrivateUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                    "
                >
                    {{ $privateFilterLabel }}

                    @if ($privateUserTableViewsCount = count($privateUserTableViews))
                        <span
                            class="pointer-events-none absolute -left-2 -top-1 h-4 w-4 select-none text-center"
                        >
                            {{ $privateUserTableViewsCount > 99 ? '99+' : $privateUserTableViewsCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'public\')',
                                'disabled' => ! $hasPublicUserTableViews,
                                'color' => $canRenderPublicUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderPublicUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                    "
                >
                    {{ $publicFilterLabel }}

                    @if ($publicUserTableViewsCount = count($publicUserTableViews))
                        <span
                            class="pointer-events-none absolute -left-2 -top-1 h-4 w-4 select-none text-center"
                        >
                            {{ $publicUserTableViewsCount > 99 ? '99+' : $publicUserTableViewsCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'system\')',
                                'disabled' => ! $hasSystemTableViews,
                                'color' => $canRenderSystemTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderSystemTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                            ->class([
                                'relative cursor-pointer select-none',
                            ])
                    "
                >
                    {{ $systemFilterLabel }}

                    @if ($systemTableViewsCount = count($systemTableViews))
                        <span
                            class="pointer-events-none absolute -left-2 -top-1 h-4 w-4 select-none text-center"
                        >
                            {{ $systemTableViewsCount > 99 ? '99+' : $systemTableViewsCount }}
                        </span>
                    @endif
                </x-filament::badge>
            </div>
        @endif
    </div>
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/@alpinejs/sort@3.x.x/dist/cdn.min.js"
    ></script>

    @if ($canRenderFavoriteUserTableViews || $canRenderPublicUserTableViews || $canRenderPrivateUserTableViews || $canRenderSystemTableViews)
        <div
            class="space-y-6 overflow-y-auto px-6 pb-6"
            style="max-height: 500px"
            @if ($isCollapsible || $isReorderable)
                x-data="{
                                @if ($isCollapsible)
             collapsedGroups: [],

                                    toggleCollapsedGroup: function (group) {
                                        if (this.isGroupCollapsed(group)) {
                                            this.collapsedGroups.splice(this.collapsedGroups.indexOf(group), 1)

                                            return
                                        }

                                        this.collapsedGroups.push(group)
                                    },

                                    isGroupCollapsed: function (group) {
                                        return this.collapsedGroups.includes(group)
                                    }, @endif


                                @if ($isReorderable)
             reorderingGroup: null,

                                    startReordering: function (group) {
                                        this.reorderingGroup = group
                                    },

                                    stopReordering: function () {
                                        this.reorderingGroup = null
                                    },

                                    isReordering: function (group) {
                                        return this.reorderingGroup === group
                                    },

                                    handleReorder: function (event) {
                                        console.log(event)
                                    }, @endif

                            }"
            @endif
        >
            @if ($canRenderFavoriteUserTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="favorite"
                    :livewireId="$livewireId"
                    :sectionHeading="$favoriteSectionHeading"
                    :tableViews="$favoriteUserTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$userTableViewActions"
                    :isCollapsible="$isCollapsible"
                    :isReorderable="$isReorderable"
                />
            @endif

            @if ($canRenderPrivateUserTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="private"
                    :livewireId="$livewireId"
                    :sectionHeading="$privateSectionHeading"
                    :tableViews="$privateUserTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$userTableViewActions"
                    :isCollapsible="$isCollapsible"
                    :isReorderable="$isReorderable"
                />
            @endif

            @if ($canRenderPublicUserTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="public"
                    :livewireId="$livewireId"
                    :sectionHeading="$publicSectionHeading"
                    :tableViews="$publicUserTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$userTableViewActions"
                    :isCollapsible="$isCollapsible"
                    :isReorderable="$isReorderable"
                />
            @endif

            @if ($canRenderSystemTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="system"
                    :livewireId="$livewireId"
                    :sectionHeading="$systemSectionHeading"
                    :tableViews="$systemTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$systemTableViewActions"
                    :isCollapsible="$isCollapsible"
                />
            @endif
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
