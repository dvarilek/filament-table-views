@props([
    'livewireId',
    'key',
    'group',
    'tableView',
    'activeTableViewKey',
    'actions',
    'isReorderable',
    'isDeferredReorderable'
])

@php
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    use Illuminate\View\ComponentAttributeBag;

    $groupValue = $group->value;
    $isDisabled = $tableView->isDisabled();

    $isUserTableView = $tableView instanceof UserView;

    $actions = array_filter($actions, static fn (Action | ActionGroup $action) => $action->isVisible());
    $hasActions = count($actions) !== 0;

    if ($isUserTableView && $hasActions) {
        $record = $tableView->getRecord();

        $actionsToProcess = collect($actions);

        while ($currentAction = $actionsToProcess->shift()) {
            if ($currentAction instanceof ActionGroup) {
                $actionsToProcess->push(...$currentAction->getFlatActions());
            } else {
                $currentAction
                    ->record($record)
                    ->arguments(['filamentTableViewsRecordKey' => $record->getKey()]);
            }
        }
    }

    $isActive = $activeTableViewKey === (string) $key;

    // TODO: See comments, when disabled it doesn't really look like disabled
    //       Fix alpine
@endphp

<div
    {{
        $attributes
            ->merge([
                'tabindex' => '0',
                'wire:loading.attr' => 'disabled',
                'disabled' => $isDisabled,
            ], false)
            ->class([
                'bg-gray-50 dark:bg-white/5' => $isActive,
                'hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10' => $isActive && ! $isDisabled,
                'hover:bg-gray-50 focus:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5 dark:focus-visible:bg-white/5' => ! $isActive && ! $isDisabled,
                'disabled:opacity-70' => $isDisabled,
                'flex h-10 rounded-lg px-2 transition duration-75',
                'flex items-center justify-between py-1 text-sm font-normal outline-none w-full select-none',
            ])
    }}
    @if (filled($tooltip = $tableView->getTooltip()))
        x-tooltip="{
            content: {{ Js::from($tooltip) }},
            theme: $store.theme,
        }"
    @endif
    @if ($isReorderable)
        x-sortable-item="{{ $key }}"
        x-bind:class="isReorderingGroup(@js($groupValue)) && ! isLoading ? 'cursor-move' : (isReorderingActive() ? 'cursor-none disabled opacity-70' : 'cursor-pointer')"
        x-bind:x-sortable-handle="isReorderingGroup(@js($groupValue)) && ! isLoading"
        @if (!$isDisabled)
            x-on:click="isReorderingActive() ? null : $wire.call('toggleActiveTableView', {{ Js::from($key) }})"
       @endif
    @elseif (!$isDisabled)
        x-bind:class="true ? 'cursor-pointer' : null" {{-- Temp ugly solution --}}
        wire:click="toggleActiveTableView({{ Js::from($key) }})"
    @endif
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
            @if ($isReorderable && $isDeferredReorderable) {{-- Maybe try a different solution --}}
                x-bind:class="isRecordReordered('{{ $key }}') ? 'text-sm font-medium uppercase tracking-wide ' : null"
            @endif
        >
            {{ $tableView->getLabel() }}
        </div>
    </div>

    <div
        class="flex w-2/5 items-center justify-end gap-2 py-1"
    >
        @if ($isUserTableView && $tableView->isFavorite())
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

        <div x-on:click.stop>
            @if ($isReorderable)
                @if ($hasActions)
                    <div
                        x-cloak
                        x-show="! isReorderingActive()"
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
            @else
                @foreach ($actions as $action)
                    {{ $action }}
                @endforeach
            @endif
        </div>
    </div>
</div>
