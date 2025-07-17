@php
    use Filament\Support\Enums\MaxWidth;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'livewireId',
    'defaultTableViews',
    'favoriteUserTableViews',
    'privateUserTableViews',
    'publicUserTableViews',
    'activeTableViewKey',
    'heading',
    'favoriteSectionHeading',
    'privateSectionHeading',
    'publicSectionHeading',
    'defaultSectionHeading',
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
    'defaultFilterLabel',
    'resetLabel',
    'defaultActions',
    'userActions',
    'isCollapsible',
    'isReorderable'
])

@php
    $hasDefaultUserTableViews = filled($defaultTableViews);
    $canRenderDefaultTableViews = $hasDefaultUserTableViews && (! $hasFilterButtons || $activeFilters['default']);

    $hasFavoriteUserTableViews = filled($favoriteUserTableViews);
    $canRenderFavoriteUserTableViews = $hasFavoriteUserTableViews && (! $hasFilterButtons || $activeFilters['favorite']);

    $hasPublicUserTableViews = filled($publicUserTableViews);
    $canRenderPublicUserTableViews = $hasPublicUserTableViews && (! $hasFilterButtons || $activeFilters['public']);

    $hasPrivateUserTableViews = filled($privateUserTableViews);
    $canRenderPrivateUserTableViews = $hasPrivateUserTableViews && (! $hasFilterButtons || $activeFilters['private']);
@endphp

<div class="flex flex-1 flex-col">
    <div class="flex flex-1 flex-col px-6 pt-6 space-y-4">
        <div class="flex justify-between">
            <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ $heading }}
            </h4>

            @if ($isSearchable || $hasFilterButtons)
                <div>
                    <x-filament::link
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new \Illuminate\View\ComponentAttributeBag([
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
                                new \Illuminate\View\ComponentAttributeBag([
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

            <div
                x-id="['input']"
                class="pt-1"
            >
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
                            new \Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'favorite\')',
                                'disabled' => ! $hasFavoriteUserTableViews,
                                'color' => $canRenderFavoriteUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderFavoriteUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                        ->class([
                            'relative cursor-pointer select-none'
                        ])
                    "
                >
                    {{ $favoriteFilterLabel }}

                    @if ($favoriteCount = count($favoriteUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $favoriteCount > 99 ? '99+' : $favoriteCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new \Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'private\')',
                                'disabled' => ! $hasPrivateUserTableViews,
                                'color' => $canRenderPrivateUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderPrivateUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                        ->class([
                            'relative cursor-pointer select-none'
                        ])
                    "
                >
                    {{ $privateFilterLabel }}

                    @if ($privateCount = count($privateUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $privateCount > 99 ? '99+' : $privateCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new \Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'public\')',
                                'disabled' => ! $hasPublicUserTableViews,
                                'color' => $canRenderPublicUserTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderPublicUserTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                        ->class([
                            'relative cursor-pointer select-none'
                        ])
                    "
                >
                    {{ $publicFilterLabel }}

                    @if ($publicCount = count($publicUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $publicCount > 99 ? '99+' : $publicCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new \Illuminate\View\ComponentAttributeBag([
                                'size' => 'sm',
                                'wire:loading.attr' => 'disabled',
                                'wire:click' => 'toggleViewManagerFilterButton(\'default\')',
                                'disabled' => ! $hasDefaultUserTableViews,
                                'color' => $canRenderDefaultTableViews ? 'primary' : 'gray',
                                'icon' => $canRenderDefaultTableViews ? 'heroicon-o-eye' : 'heroicon-o-eye-slash',
                            ])
                        )
                        ->class([
                            'relative cursor-pointer select-none'
                        ])
                    "
                >
                    {{ $defaultFilterLabel }}

                    @if ($defaultCount = count($defaultTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $defaultCount > 99 ? '99+' : $defaultCount }}
                        </span>
                    @endif
                </x-filament::badge>
            </div>
        @endif
    </div>

    @if ($canRenderFavoriteUserTableViews || $canRenderPublicUserTableViews || $canRenderPrivateUserTableViews || $canRenderDefaultTableViews)
        <div
            class="space-y-6 overflow-y-auto p-6"
            style="max-height: 500px"
            @if ($isCollapsible)
                x-data="{
                    collapsedGroups: [],

                    toggleCollapsedGroup: function (group) {
                        if (this.isGroupCollapsed(group)) {
                            this.collapsedGroups.splice(
                                this.collapsedGroups.indexOf(group),
                                1,
                            )

                            return
                        }

                        this.collapsedGroups.push(group)
                    },

                    isGroupCollapsed: function (group) {
                        return this.collapsedGroups.includes(group)
                    },
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
                    :actions="$userActions"
                    :isCollapsible="$isCollapsible"
                />
            @endif

            @if ($canRenderPrivateUserTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="private"
                    :livewireId="$livewireId"
                    :sectionHeading="$privateSectionHeading"
                    :tableViews="$privateUserTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$userActions"
                    :isCollapsible="$isCollapsible"
                />
            @endif

            @if ($canRenderPublicUserTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="public"
                    :livewireId="$livewireId"
                    :sectionHeading="$publicSectionHeading"
                    :tableViews="$publicUserTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$userActions"
                    :isCollapsible="$isCollapsible"
                />
            @endif

            @if ($canRenderDefaultTableViews)
                <x-filament-table-views::manager.table-view-section
                    section="default"
                    :livewireId="$livewireId"
                    :sectionHeading="$defaultSectionHeading"
                    :tableViews="$defaultTableViews"
                    :activeTableViewKey="$activeTableViewKey"
                    :actions="$defaultActions"
                    :isCollapsible="$isCollapsible"
                />
            @endif
        </div>
    @else
        <div class="mx-auto grid max-w-lg justify-items-center text-center">
            <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-500/20 my-4">
                <x-filament::icon
                    icon="heroicon-m-x-mark"
                    class="h-6 w-6 text-gray-500 dark:text-gray-400"
                />
            </div>

            @if ($emptyStatePlaceholder)
                <h4 class="text-md font-normal leading-6 pb-6">
                    {{ $emptyStatePlaceholder }}
                </h4>
            @endif
        </div>
    @endif
</div>
