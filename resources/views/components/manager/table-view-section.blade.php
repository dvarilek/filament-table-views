@props([
    'section',
    'livewireId',
    'sectionHeading',
    'tableViews',
    'activeTableViewKey',
    'actions',
    'isCollapsible',
])

@php
    use Dvarilek\FilamentTableViews\Components\TableView\UserView;
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    use Illuminate\View\ComponentAttributeBag;
@endphp

<div
    class="space-y-2"
    @if ($isCollapsible)
        x-bind:aria-expanded="! isGroupCollapsed(@js($section))"
        aria-expanded="true"
    @endif
>
    <div class="flex items-center justify-between">
        <div
            @class([
                'cursor-pointer' => $isCollapsible,
                'flex items-center gap-x-2',
            ])
            @if ($isCollapsible)
                x-on:click="toggleCollapsedGroup(@js($section))"
            @endif
        >
            @if ($sectionHeading)
                <h5
                    class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    {{ $sectionHeading }}
                </h5>
            @endif

            @if ($isCollapsible)
                <x-filament::icon
                    icon="heroicon-o-chevron-up"
                    class="h-4 w-4 text-gray-500 dark:text-gray-400"
                    x-bind:class="isGroupCollapsed('{{ $section }}') && '-rotate-180'"
                />
            @endif
        </div>

        <div>
            {{-- Add sort --}}
        </div>
    </div>

    <div
        class="space-y-1"
        @if ($isCollapsible)
            x-show="! isGroupCollapsed(@js($section))"
            x-transition
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
                            'wire:key' => 'filament-table-views-manager-' . $section . '-view-' . $key . '-' . $livewireId,
                        ], false)
                        ->class([
                            'bg-gray-50 dark:bg-white/5' => $isActive,
                            'hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10' => $isActive && ! $isDisabled,
                            'hover:bg-gray-50 focus:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5 dark:focus-visible:bg-white/5' => ! $isActive && ! $isDisabled,
                            'disabled:opacity-70' => $isDisabled,
                            'flex h-10 rounded-lg px-2 transition duration-75',
                        ])
                }}
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

                @if ($hasActions)
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

                        @foreach ($actions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
