@props([
    'livewireId',
    'key',
    'group',
    'tableView',
    'isActive',
    'actions',
    'cacheRecordToAction',
    'isReorderable',
    'isDeferredReorderable',
    'isHighlightingReorderedRecords'
])

@php
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    use Illuminate\View\ComponentAttributeBag;

    $groupValue = $group->value;
    $color = $tableView->getColor();
    $isDisabled = $tableView->isDisabled();

    $isUserView = $tableView instanceof UserView;
    $hasActions = count($actions) !== 0;

    if ($isUserView && $hasActions) {
        $cacheRecordToAction($actions, $tableView->getRecord());
    }

    $actions = array_filter($actions, static fn (Action | ActionGroup $action) => $action->isVisible());

    $enabledHoverCssClasses = 'hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10 transition duration-75';
@endphp

<div
    {{
        $attributes
            ->class([
                'bg-gray-100 dark:bg-white/10' => $isActive,
                'opacity-70' => $isDisabled,
                 $enabledHoverCssClasses . ' cursor-pointer' => ! $isDisabled && ! $isReorderable,
                'flex items-center justify-between py-1 text-sm font-normal outline-none w-full select-none h-10 rounded-s-lg px-2',
            ])
            ->style([
                'cursor: not-allowed' => $isDisabled,
                 \Filament\Support\get_color_css_variables(
                   $color,
                   shades: [400, 500, 600],
               ) => $color,
               'border-left: 2px solid rgb(var(--c-600))' => $color && $isActive,
               'border-left: 2px solid rgb(var(--c-400))' => $color && ! $isActive,
            ])
    }}
    @if (filled($tooltip = $tableView->getTooltip()))
        x-tooltip="{
            content: {{ Js::from($tooltip) }},
            theme: $store.theme,
        }"
    @endif
    @if (! $isDisabled)
        @if ($isReorderable)
            x-sortable-item="{{ $key }}"
            x-bind:class="{
                '{{ $enabledHoverCssClasses }}': (isReorderingGroup(@js($groupValue)) && !isLoading) || !isReorderingActive(),
                'cursor-move': isReorderingGroup(@js($groupValue)) && ! isLoading,
                'cursor-pointer': !isReorderingActive(),
                'opacity-70': isReorderingActive() && (!isReorderingGroup(@js($groupValue)) || isLoading),
            }"
            x-bind:style="{
                'cursor': isReorderingActive() && (!isReorderingGroup(@js($groupValue)) || isLoading) ? 'not-allowed' : null,
            }"
            x-bind:x-sortable-handle="isReorderingGroup(@js($groupValue)) && ! isLoading"
            x-on:click="isReorderingActive() ? null : $wire.call('toggleActiveTableView', {{ Js::from($key) }})"
        @else
            wire:click="toggleActiveTableView({{ Js::from($key) }})"
        @endif
    @endif
    role="button"
>
    <div class="flex items-center gap-x-1.5">
        @if ($icon = $tableView->getIcon())
            <x-filament::icon
                class="w-5 h-5"
                :icon="$icon"
            />
        @endif

        <div
            class="truncate p-0.5"
            @if ($isReorderable && $isDeferredReorderable && $isHighlightingReorderedRecords) {{-- Maybe try a different solution --}}
                x-bind:class="isRecordReordered('{{ $key }}') ? 'text-sm font-medium uppercase tracking-wide ' : null"
            @endif
        >
            {{ $tableView->getLabel() }}
        </div>
    </div>

    <div
        class="flex w-2/5 items-center justify-end gap-2 py-1"
    >
        @if ($isUserView && $tableView->isFavorite())
            @if ($tableView->isPublic())
                <x-filament::icon
                    class="w-4 h-4"
                    color="gray"
                    icon="heroicon-o-eye"
                    :title="__('filament-table-views::toolbar.actions.manage-table-views.group_item_badges.public')"
                />
            @else
                <x-filament::icon
                    class="w-4 h-4"
                    color="gray"
                    icon="heroicon-o-eye-slash"
                    :title="__('filament-table-views::toolbar.actions.manage-table-views.group_item_badges.private')"
                />
            @endif
        @endif

        @if ($tableView->isDefault())
            <x-filament::icon
                class="w-4 h-4"
                color="primary"
                icon="heroicon-o-bookmark"
                :title="__('filament-table-views::toolbar.actions.manage-table-views.group_item_badges.default')"
            />
        @endif

        @if (! $isDisabled)
            <div x-on:click.stop>
                @if ($isReorderable)
                    @if ($hasActions)
                        <div
                            x-cloak
                            x-show="! isReorderingActive()"
                            class="flex items-center gap-1"
                        >
                            @foreach ($actions as $action)
                                {{ $action }}
                            @endforeach
                        </div>
                    @endif

                    <div
                        x-cloak
                        x-show="isReorderingGroup(@js($groupValue))"
                    >
                        <x-filament::icon
                            icon="heroicon-o-bars-2"
                            color="gray"
                            class="h-5 w-5 text-gray-500 dark:text-gray-400"
                        />
                    </div>
                @elseif ($hasActions)
                    <div
                        class="flex items-center gap-1"
                    >
                        @foreach ($actions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
