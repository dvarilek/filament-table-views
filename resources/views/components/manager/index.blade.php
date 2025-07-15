@php
    use Filament\Support\Enums\MaxWidth;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'livewireId',
    'defaultTableViews',
    'userTableViews',
    'activeTableViewKey',
    'tableViewManagerSearch',
    'tableViewManagerSearchDebounce' => '500ms',
    'tableViewManagerSearchOnBlur' => false,
    'tableViewManagerSearchLabel',
    'tableViewManagerSearchPlaceholder',
    'tableViewManagerActiveFilters',
    'tableViewManagerDefaultActions',
    'tableViewManagerUserActions',
])

@php
    $tableViewManagerSearchWireModelAttribute = $tableViewManagerSearchOnBlur ? 'wire:model.blur' : "wire:model.live.debounce.{$tableViewManagerSearchDebounce}";

    $favoriteUserTableViews = [];
    $publicUserTableViews = [];
    $personalUserTableViews = [];

    foreach ($userTableViews as $key => $tableView) {
        if ($tableView->isFavorite()) {
            $favoriteUserTableViews[$key] = $tableView;
        } elseif ($tableView->isPublic()) {
            $publicUserTableViews[$key] = $tableView;
        } else {
            $personalUserTableViews[$key] = $tableView;
        }
    }

    $hasDefaultUserTableViews = filled($defaultTableViews);
    $hasDefaultTableViewsFilterActive = $tableViewManagerActiveFilters['default'];
    $canRenderDefaultTableViews = $hasDefaultUserTableViews && $hasDefaultTableViewsFilterActive;

    $hasFavoriteUserTableViews = filled($favoriteUserTableViews);
    $hasFavoriteTableViewsFilterActive = $tableViewManagerActiveFilters['favorite'];
    $canRenderFavoriteUserTableViews = $hasFavoriteUserTableViews && $hasFavoriteTableViewsFilterActive;

    $hasPublicUserTableViews = filled($publicUserTableViews);
    $hasPublicTableViewsFilterActive = $tableViewManagerActiveFilters['public'];
    $canRenderPublicUserTableViews = $hasPublicUserTableViews && $hasPublicTableViewsFilterActive;

    $hasPersonalUserTableViews = filled($personalUserTableViews);
    $hasPersonalTableViewsFilterActive = $tableViewManagerActiveFilters['personal'];
    $canRenderPersonalUserTableViews = $hasPersonalUserTableViews && $hasPersonalTableViewsFilterActive;
@endphp

<div class="flex flex-1 flex-col p-6 space-y-6">
    <div class="flex flex-1 flex-col space-y-4">
        <div class="flex justify-between">
            <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                @lang('filament-table-views::toolbar.actions.manage-table-views.label')
            </h4>

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
                    @lang('filament-table-views::toolbar.actions.manage-table-views.reset_label')
                </x-filament::link>

                <x-filament::loading-indicator
                    :attributes="
                    \Filament\Support\prepare_inherited_attributes(
                        new \Illuminate\View\ComponentAttributeBag([
                            'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                            'wire:target' => 'resetTableViewManager',
                        ])
                    )->class(['h-5 w-5 text-gray-400 dark:text-gray-500'])
                "
                />
            </div>
        </div>

        <div
            x-id="['input']"
            class="pt-1"
        >
            <label x-bind:for="$id('input')" class="sr-only">
                {{ $tableViewManagerSearchLabel }}
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
                        'placeholder' => $tableViewManagerSearchPlaceholder,
                        'type' => 'search',
                        'wire:key' => 'filament-table-views-manager-search-field-' . $livewireId . 'tableViewManagerSearch',
                        $tableViewManagerSearchWireModelAttribute => 'tableViewManagerSearch',
                        'x-bind:id' => '$id(\'input\')',
                        'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                    ])
                "
                />
            </x-filament::input.wrapper>
        </div>

        <div class="flex flex-1 justify-between items-start gap-x-4">
            <x-filament::badge
                size="sm"
                class="relative cursor-pointer select-none"
                wire:loading.attr="disabled"
                wire:click="toggleViewManagerFilterButton('default')"
                :disabled="! $hasDefaultUserTableViews"
                :color="$canRenderDefaultTableViews ? 'primary' : 'gray'"
            >
                @lang('filament-table-views::toolbar.actions.manage-table-views.filters.default')

                @if ($defaultCount = count($defaultTableViews))
                    <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                        {{ $defaultCount > 99 ? '99+' : $defaultCount }}
                    </span>
                @endif
            </x-filament::badge>

            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <x-filament::badge
                    size="sm"
                    class="relative cursor-pointer select-none"
                    wire:loading.attr="disabled"
                    wire:click="toggleViewManagerFilterButton('favorite')"
                    :disabled="! $hasFavoriteUserTableViews"
                    :color="$canRenderFavoriteUserTableViews ? 'primary' : 'gray'"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.favorite')

                    @if ($favoriteCount = count($favoriteUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $favoriteCount > 99 ? '99+' : $favoriteCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    size="sm"
                    class="relative cursor-pointer select-none"
                    wire:loading.attr="disabled"
                    wire:click="toggleViewManagerFilterButton('public')"
                    :disabled="! $hasPublicUserTableViews"
                    :color="$canRenderPublicUserTableViews ? 'primary' : 'gray'"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.public')

                    @if ($publicCount = count($publicUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $publicCount > 99 ? '99+' : $publicCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    size="sm"
                    class="relative cursor-pointer select-none"
                    wire:loading.attr="disabled"
                    wire:click="toggleViewManagerFilterButton('personal')"
                    :disabled="! $hasPersonalUserTableViews"
                    :color="$canRenderPersonalUserTableViews ? 'primary' : 'gray'"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.personal')

                    @if ($personalCount = count($personalUserTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $personalCount > 99 ? '99+' : $personalCount }}
                        </span>
                    @endif
                </x-filament::badge>
            </div>
        </div>
    </div>

    @if ($canRenderFavoriteUserTableViews || $canRenderPublicUserTableViews || $canRenderPersonalUserTableViews || $canRenderDefaultTableViews)
        <div class="space-y-6 overflow-y-auto" style="max-height: 400px">
            @if ($canRenderFavoriteUserTableViews)
                <div class="space-y-2">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.favorite')
                    </h5>

                    <div class="space-y-1">
                        @foreach($favoriteUserTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :wire-key="'filament-table-views-manager-favorite-user-view-' . $key . '-' . $livewireId"
                                :key="$key"
                                :tableView="$tableView"
                                :isActive="$activeTableViewKey === (string) $key"
                                :actions="$tableViewManagerDefaultActions"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderPersonalUserTableViews)
                <div class="space-y-2">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.personal')
                    </h5>

                    <div class="space-y-1">
                        @foreach($personalUserTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :wire-key="'filament-table-views-manager-personal-user-view-' . $key . '-' . $livewireId"
                                :key="$key"
                                :tableView="$tableView"
                                :isActive="$activeTableViewKey === (string) $key"
                                :actions="$tableViewManagerDefaultActions"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderPublicUserTableViews)
                <div class="space-y-2">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.public')
                    </h5>

                    <div class="space-y-1">
                        @foreach($publicUserTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :wire-key="'filament-table-views-manager-public-user-view-' . $key . '-' . $livewireId"
                                :key="$key"
                                :tableView="$tableView"
                                :isActive="$activeTableViewKey === (string) $key"
                                :actions="$tableViewManagerUserActions"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderDefaultTableViews)
                <div class="space-y-2">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.default')
                    </h5>

                    <div class="space-y-1">
                        @foreach($defaultTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :wire-key="'filament-table-views-manager-default-view-' . $key . '-' . $livewireId"
                                :key="$key"
                                :tableView="$tableView"
                                :isActive="$activeTableViewKey === (string) $key"
                                :actions="$tableViewManagerDefaultActions"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @else
        <div
            class="mx-auto grid max-w-lg justify-items-center text-center"
        >
            <div
                class="rounded-full bg-gray-100 p-3 dark:bg-gray-500/20 my-4"
            >
                <x-filament::icon
                    icon="heroicon-m-x-mark"
                    class="h-6 w-6 text-gray-500 dark:text-gray-400"
                />
            </div>

            <h4
                class="text-md font-normal leading-6 mb-4"
            >
                {{
                    $tableViewManagerSearch !== ''
                        ? __('filament-table-views::toolbar.actions.manage-table-views.empty-state.search_empty_state')
                        : __('filament-table-views::toolbar.actions.manage-table-views.empty-state.no_views_empty_state')
                }}
            </h4>
        </div>
    @endif
</div>
