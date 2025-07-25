@props([
    'group',
    'livewireId',
    'groupHeading',
    'tableViews',
    'activeTableViewKey',
    'actions',
    'isCollapsible',
    'isReorderable' => false,
])

@php
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    use Illuminate\View\ComponentAttributeBag;

    $groupValue = $group->value;
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between">
        <div
            @class([
                'cursor-pointer' => $isCollapsible,
                'flex items-center gap-x-2',
            ])
            @if ($isCollapsible)
                x-on:click="toggleCollapsedGroup(@js($groupValue))"
            @endif
        >
            @if ($groupHeading)
                <h5
                    class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    {{ $groupHeading }}
                </h5>
            @endif

            @if ($isCollapsible)
                <x-filament::icon
                    icon="heroicon-o-chevron-up"
                    class="h-4 w-4 text-gray-500 dark:text-gray-400"
                    x-bind:class="isGroupCollapsed('{{ $groupValue }}') && '-rotate-180'"
                />
            @endif
        </div>

        @if ($isReorderable)
            <div style="padding-right: 0.5rem">
                <x-filament::icon
                    x-cloak
                    x-show="isReordering('{{ $groupValue }}')"
                    icon="heroicon-o-x-mark"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    x-on:click="stopReordering('{{ $groupValue }}')"
                />
                <x-filament::icon
                    x-cloak
                    x-show="! isReordering('{{ $groupValue }}')"
                    icon="heroicon-o-arrows-up-down"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    x-on:click="startReordering('{{ $groupValue }}')"
                />
            </div>
        @endif
    </div>

    <div
        class="space-y-1"
        @if ($isCollapsible)
            x-show="! isGroupCollapsed(@js($groupValue))"
            x-bind:aria-expanded="! isGroupCollapsed(@js($groupValue))"
            aria-expanded="true"
            x-transition
        @endif
        @if ($isReorderable)
            x-sortable
            x-on:end.stop="handleGroupReorder(@js($groupValue), $event)"
        @endif
    >
        @foreach ($tableViews as $key => $tableView)
            @php
                $label = $tableView->getLabel();
                $tooltip = $tableView->getTooltip();
                $color = $tableView->getColor();
                $icon = $tableView->getIcon();
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
                            'wire:key' => 'filament-table-views-manager-' . $groupValue . '-view-' . $key . '-' . $livewireId,
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
                    x-bind:x-sortable-handle="isReordering('{{ $groupValue }}')"
                @endif
            >
                <button
                    class="flex items-center justify-start gap-x-1.5 py-1 text-sm font-normal outline-none"
                    type="button"
                    @disabled($isDisabled)
                    @if (filled($tooltip))
                        x-tooltip="{
                            content: {{ Js::from($tooltip) }},
                            theme: $store.theme,
                        }"
                    @endif
                    @style([
                        'width: 70%' => $hasActions,
                        'width: 100%' => ! $hasActions,
                    ])
                    wire:click="toggleActiveTableView({{ Js::from($key) }})"
                    wire:loading.attr="disabled"
                >
                    @if ($icon)
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
                        {{ $label }}
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
                                            'title' => __('filament-table-views::toolbar.actions.manage-table-views.sections.public'),
                                        ])
                                    )
                                        ->class([
                                            'h-5 w-5',
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
                                            'title' => __('filament-table-views::toolbar.actions.manage-table-views.sections.private'),
                                        ])
                                    )
                                        ->class([
                                            'h-5 w-5',
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
                                        'title' => __('filament-table-views::toolbar.actions.manage-table-views.sections.default'),
                                    ])
                                )
                                    ->class([
                                        'h-5 w-5',
                                    ])
                            "
                        />
                    @endif

                    @if ($isReorderable)
                        @if ($hasActions)
                            <div
                                x-cloak
                                x-show="! isReordering('{{ $groupValue }}')"
                            >
                                @foreach ($actions as $action)
                                    {{ $action }}
                                @endforeach
                            </div>
                        @endif

                        <div x-cloak x-show="isReordering('{{ $groupValue }}')">
                            <x-filament::icon
                                icon="heroicon-o-bars-2"
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
        @endforeach
    </div>
</div>
