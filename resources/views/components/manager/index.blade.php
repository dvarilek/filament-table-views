@php
    use Filament\Support\Enums\MaxWidth;
    use Illuminate\View\ComponentAttributeBag;

@endphp

@props([
    'livewireId',
    'customTableViews',
    'defaultTableViews',
    'customTableViewActions',
    'activeTableViewKey',
    'tableViewManagerSearch',
    'tableViewManagerSearchDebounce' => '500ms',
    'tableViewManagerSearchOnBlur' => false,
    'tableViewManagerSearchLabel',
    'tableViewManagerSearchPlaceholder',
    'tableViewManagerActiveFilters'
])

@php
    $wireModelAttribute = $tableViewManagerSearchOnBlur ? 'wire:model.blur' : "wire:model.live.debounce.{$tableViewManagerSearchDebounce}";

    $favoriteCustomTableViews = [];
    $publicCustomTableViews = [];
    $personalCustomTableViews = [];

    foreach ($customTableViews as $key => $tableView) {
        if ($tableView->isFavorite()) {
            $favoriteCustomTableViews[$key] = $tableView;
        } elseif ($tableView->isPublic()) {
            $publicCustomTableViews[$key] = $tableView;
        } else {
            $personalCustomTableViews[$key] = $tableView;
        }
    }

    $hasDefaultTableViewsFilterActive = $tableViewManagerActiveFilters['default'];
    $canRenderDefaultTableViews = filled($defaultTableViews) && $hasDefaultTableViewsFilterActive;

    $hasFavoriteTableViewsFilterActive = $tableViewManagerActiveFilters['favorite'];
    $canRenderFavoriteCustomTableViews = filled($favoriteCustomTableViews) && $hasFavoriteTableViewsFilterActive;

    $hasPublicTableViewsFilterActive = $tableViewManagerActiveFilters['public'];
    $canRenderPublicCustomTableViews = filled($publicCustomTableViews) && $hasPublicTableViewsFilterActive;

    $hasPersonalTableViewsFilterActive = $tableViewManagerActiveFilters['personal'];
    $canRenderPersonalCustomTableViews = filled($personalCustomTableViews) && $hasPersonalTableViewsFilterActive;
@endphp

<div class="flex flex-1 flex-col p-4 space-y-4">
    <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
        @lang('filament-table-views::toolbar.actions.manage-table-views.label')
    </h4>

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
                        $wireModelAttribute => 'tableViewManagerSearch',
                        'x-bind:id' => '$id(\'input\')',
                        'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                    ])
                "
            />
        </x-filament::input.wrapper>
    </div>

    @if ($canRenderFavoriteCustomTableViews || $canRenderPersonalCustomTableViews || $canRenderPublicCustomTableViews)
        <div
            class="flex flex-1 justify-between items-start gap-x-4"
        >
            <x-filament::badge
                size="sm"
                class="relative cursor-pointer select-none"
                :color="$hasDefaultTableViewsFilterActive ? 'primary' : 'gray'"
                wire:click="toggleViewManagerFilterButton('default')"
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
                    :color="$hasFavoriteTableViewsFilterActive ? 'primary' : 'gray'"
                    wire:click="toggleViewManagerFilterButton('favorites')"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.favorite')

                    @if ($favoriteCount = count($favoriteCustomTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $favoriteCount > 99 ? '99+' : $favoriteCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    size="sm"
                    class="relative cursor-pointer select-none"
                    :color="$hasPublicTableViewsFilterActive ? 'primary' : 'gray'"
                    wire:click="toggleViewManagerFilterButton('public')"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.public')

                    @if ($publicCount = count($publicCustomTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $publicCount > 99 ? '99+' : $publicCount }}
                        </span>
                    @endif
                </x-filament::badge>

                <x-filament::badge
                    size="sm"
                    class="relative cursor-pointer select-none"
                    :color="$hasPersonalTableViewsFilterActive ? 'primary' : 'gray'"
                    wire:click="toggleViewManagerFilterButton('personal')"
                >
                    @lang('filament-table-views::toolbar.actions.manage-table-views.filters.personal')

                    @if ($personalCount = count($personalCustomTableViews))
                        <span class='absolute -top-1 -left-2 h-4 w-4 text-center pointer-events-none select-none'>
                            {{ $personalCount > 99 ? '99+' : $personalCount }}
                        </span>
                    @endif
                </x-filament::badge>
            </div>
        </div>

        <div class="space-y-4 overflow-y-auto" style="max-height: 400px">
            @if ($canRenderFavoriteCustomTableViews)
                <div class="space-y-3">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.favorite')
                    </h5>

                    <div class="space-y-1">
                        @foreach($favoriteCustomTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :tableView="$tableView"
                                :key="$key"
                                :isActive="$activeTableViewKey === (string) $key"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderPersonalCustomTableViews)
                <div class="space-y-3">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.personal')
                    </h5>

                    <div class="space-y-1">
                        @foreach($personalCustomTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :tableView="$tableView"
                                :key="$key"
                                :isActive="$activeTableViewKey === (string) $key"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderPublicCustomTableViews)
                <div class="space-y-3">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.public')
                    </h5>

                    <div class="space-y-1">
                        @foreach($publicCustomTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :tableView="$tableView"
                                :key="$key"
                                :isActive="$activeTableViewKey === (string) $key"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canRenderDefaultTableViews)
                <div class="space-y-3">
                    <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        @lang('filament-table-views::toolbar.actions.manage-table-views.sections.default')
                    </h5>

                    <div class="space-y-1">
                        @foreach($defaultTableViews as $key => $tableView)
                            <x-filament-table-views::manager.table-view-item
                                :tableView="$tableView"
                                :key="$key"
                                :isActive="$activeTableViewKey === (string) $key"
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
