@props([
    'groupValue' => null,
    'isCollapsible' => false,
    'isDeferredReorderable',
    'isMultiGroupReorderable'
])

@php
    $loadingLabel = __('filament-table-views::toolbar.actions.manage-table-views.reordering.loading_indicator');
    $confirmReorderingLabel = __('filament-table-views::toolbar.actions.manage-table-views.reordering.confirm_reordering');
    $stopReorderingLabel = __('filament-table-views::toolbar.actions.manage-table-views.reordering.stop_reordering');
@endphp

<div
    class="flex items-center gap-x-2"
    x-bind:class="isLoading ? null : 'cursor-pointer'"
    x-on:click="toggleReordering(@js($groupValue))"
    @if ($isCollapsible && ! $isMultiGroupReorderable)
        x-cloak
        x-show="! isGroupCollapsed(@js($groupValue))"
    @endif
>
    <div
        x-cloak
        x-show="isReorderingActive(@js($groupValue))"
        class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
        x-text="isLoading ? @js($loadingLabel) : (
                (isDeferredReorderable && hasPendingReorderedRecords())
                    ? @js($confirmReorderingLabel)
                    : @js($stopReorderingLabel)
            )"
        )"
    >

    </div>

    <div
        x-cloak
        @if ($isMultiGroupReorderable)
            x-show="! isReorderingActive(@js($groupValue))"
        @else
            x-show="! isReorderingActive(@js($groupValue)) && ! activeReorderingGroup"
        @endif
    >
        <x-filament::icon
            icon="heroicon-o-arrows-up-down"
            class="h-5 w-5 text-gray-500 dark:text-gray-400"
        />
    </div>

    <div
        x-cloak
        x-show="isReorderingActive(@js($groupValue))"
    >
        <x-filament::loading-indicator
            :attributes="
                \Filament\Support\prepare_inherited_attributes(
                    new \Illuminate\View\ComponentAttributeBag([
                        'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                        'wire:target' => 'reorderTableViewsInGroups, reorderTableViewsInGroup',
                    ])
                )
                    ->class([
                        'h-5 w-5'
                    ])
                "
        />

        @if ($isDeferredReorderable)
            <x-filament::icon
                x-cloak
                x-show="! hasPendingReorderedRecords()"
                icon="heroicon-o-x-mark"
                class="h-5 w-5 text-gray-500 dark:text-gray-400"
            />

            <x-filament::icon-button
                x-cloak
                x-show="! isLoading && hasPendingReorderedRecords()"
                icon-color="primary"
                icon="heroicon-o-check"
                class="h-5 w-5"
            />
        @else
            <x-filament::icon
                x-cloak
                x-show="! isLoading"
                icon="heroicon-o-x-mark"
                class="h-5 w-5 text-gray-500 dark:text-gray-400"
            />
        @endif
    </div>
</div>
