@props([
    'livewireId',
    'key',
    'group',
    'tableView',
    'activeTableViewKey',
    'actions',
    'isReorderable',
    'isMultiGroupReorderable',
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
        $record = $tableView->getTableView();

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
@endphp

<div
    {{
        $attributes
            ->merge([
                'disabled' => $isDisabled,
            ], false)
            ->class([
                'bg-gray-50 dark:bg-white/5' => $isActive,
                'hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10' => $isActive && ! $isDisabled,
                'hover:bg-gray-50 focus:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5 dark:focus-visible:bg-white/5' => ! $isActive && ! $isDisabled,
                'disabled:opacity-70' => $isDisabled,
                'flex h-10 rounded-lg px-2 transition duration-75',
            ])
    }}
    @if ($isReorderable)
        x-sortable-item="{{ $key }}"

        @if ($isMultiGroupReorderable)
            x-bind:class="isMultiGroupReordering() && ! isLoading ? 'cursor-move' : null"
            x-bind:x-sortable-handle="isMultiGroupReordering() && ! isLoading"
        @else
            x-bind:class="isGroupReordering('{{ $groupValue }}') && ! isLoading ? 'cursor-move' : null"
            x-bind:x-sortable-handle="isGroupReordering('{{ $groupValue }}') && ! isLoading"
        @endif
    @endif
>
    <button
        class="flex items-center justify-start gap-x-1.5 py-1 text-sm font-normal outline-none"
        type="button"
        @disabled($isDisabled)
        @if (filled($tooltip = $tableView->getTooltip()))
            x-tooltip="{
                content: {{ Js::from($tooltip) }},
                theme: $store.theme,
            }"
        @endif
        @style([
            'width: 70%' => $hasActions,
            'width: 100%' => ! $hasActions,
        ])
        @if ($isReorderable)
            @if ($isMultiGroupReorderable)
                x-bind:class="isMultiGroupReordering() && ! isLoading ? 'cursor-move' : null"
                x-on:click="
                    isMultiGroupReordering()
                        ? null
                        : $wire.call('toggleActiveTableView', {{ Js::from($key) }})
                "
            @else
                x-bind:class="isGroupReordering('{{ $groupValue }}') && ! isLoading ? 'cursor-move' : null"
                x-on:click="
                    isGroupReordering('{{ $groupValue }}')
                        ? null
                        : $wire.call('toggleActiveTableView', {{ Js::from($key) }})
                "
            @endif
        @else
            wire:click="toggleActiveTableView({{ Js::from($key) }})"
        @endif
        wire:loading.attr="disabled"
    >
        @if ($icon = $tableView->getIcon())
            <x-filament::icon
                :attributes="
                    \Filament\Support\prepare_inherited_attributes(
                        new ComponentAttributeBag([
                            'icon' => $icon,
                        ])
                    )
                        ->class([
                            'h-5 w-5',
                        ])
                "
            />
        @endif

        <div class="truncate p-0.5">
            {{ $tableView->getLabel() }}
        </div>
    </button>

    <div
        class="flex w-2/5 items-center justify-end gap-2 py-1"
        style="width: 30%"
    >
        @if ($isUserTableView && $tableView->isFavorite())
            @if ($tableView->isPublic())
                <x-filament::icon
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new ComponentAttributeBag([
                                'color' => 'gray',
                                'icon' => 'heroicon-o-eye',
                                'title' => __('filament-table-views::toolbar.actions.manage-table-views.groups.public'),
                            ])
                        )
                            ->class([
                                'h-4 w-4',
                            ])
                    "
                />
            @else
                <x-filament::icon-button
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes(
                            new ComponentAttributeBag([
                                'color' => 'gray',
                                'icon' => 'heroicon-o-eye-slash',
                                'title' => __('filament-table-views::toolbar.actions.manage-table-views.groups.private'),
                            ])
                        )
                            ->class([
                                'h-4 w-4',
                            ])
                    "
                />
            @endif
        @endif

        @if ($tableView->isDefault())
            <x-filament::icon
                :attributes="
                    \Filament\Support\prepare_inherited_attributes(
                        new ComponentAttributeBag([
                            'color' => 'gray',
                            'icon' => 'heroicon-o-bookmark',
                            'title' => __('filament-table-views::toolbar.actions.manage-table-views.groups.system'),
                        ])
                    )
                        ->class([
                            'h-4 w-4',
                        ])
                "
            />
        @endif

        @if ($isReorderable)
            @if ($hasActions)
                @if ($isMultiGroupReorderable)
                    <div x-cloak x-show="! isMultiGroupReordering()">
                        @foreach ($actions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @else
                    <div
                        x-cloak
                        x-show="! isGroupReordering('{{ $groupValue }}')"
                    >
                        @foreach ($actions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            @endif

            @if ($isMultiGroupReorderable)
                <div x-cloak x-show="isMultiGroupReordering()">
                    <x-filament::icon
                        icon="heroicon-o-bars-2"
                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    />
                </div>
            @else
                <div x-cloak x-show="isGroupReordering('{{ $groupValue }}')">
                    <x-filament::icon
                        icon="heroicon-o-bars-2"
                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    />
                </div>
            @endif
        @else
            @foreach ($actions as $action)
                {{ $action }}
            @endforeach
        @endif
    </div>
</div>
