@props([
    'groupValue' => null,
    'isCollapsible',
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
    @if ($isCollapsible)
        x-cloak
        x-show="! isGroupCollapsed('{{ $groupValue }}')"
    @endif
    @if ($isMultiGroupReorderable)
        x-on:click="toggleMultiGroupReordering()"
    @else
        style="padding-right: 0.5rem"
        x-on:click="toggleGroupReordering('{{ $groupValue }}')"
    @endif
>
    <div
        x-cloak
        @if ($isMultiGroupReorderable)
            x-show="isMultiGroupReordering()"
        @else
            x-show="isGroupReordering('{{ $groupValue }}')"
        @endif
        class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
        x-text="isLoading ? @js($loadingLabel) : (
                (isDeferredReorderable && (isMultiGroupReorderable ? pendingReorderingOrders.size : pendingReorderingOrder.size))
                    ? @js($confirmReorderingLabel)
                    : @js($stopReorderingLabel)
            )"
        )"
    >

    </div>

    <div
        x-cloak
        @if ($isMultiGroupReorderable)
            x-show="!isMultiGroupReordering()"
        @else
            x-show="!isGroupReordering('{{ $groupValue }}') && activeReorderingGroup === null"
        @endif
    >
        <x-filament::icon
            icon="heroicon-o-arrows-up-down"
            class="h-5 w-5 text-gray-500 dark:text-gray-400"
        />
    </div>

    <div
        x-cloak
        @if ($isMultiGroupReorderable)
            x-show="isMultiGroupReordering()"
        @else
            x-show="isGroupReordering('{{ $groupValue }}')"
        @endif
    >
        <x-filament::loading-indicator
            :attributes="
                \Filament\Support\prepare_inherited_attributes(
                    new \Illuminate\View\ComponentAttributeBag([
                        'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => '',
                        'wire:target' => $isMultiGroupReorderable ? 'reorderTableViewsInGroups, reorderTableViewsInGroup' : 'reorderTableViewsInGroup',
                    ])
                )
                    ->class([
                        'h-5 w-5'
                    ])
                "
        />

        @if ($isDeferredReorderable)
            @if ($isMultiGroupReorderable)
                <x-filament::icon
                    x-cloak
                    x-show="! pendingReorderingOrders.size"
                    icon="heroicon-o-x-mark"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                />

                <x-filament::icon-button
                    x-cloak
                    x-show="! isLoading && pendingReorderingOrders.size"
                    icon-color="primary"
                    icon="heroicon-o-check"
                    class="h-5 w-5"
                />
            @else
                <x-filament::icon
                    x-cloak
                    x-show="! pendingReorderingOrder.size"
                    icon="heroicon-o-x-mark"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                />

                <x-filament::icon-button
                    x-cloak
                    x-show="! isLoading && pendingReorderingOrder.size"
                    icon-color="primary"
                    icon="heroicon-o-check"
                    class="h-5 w-5"
                />
            @endif
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
