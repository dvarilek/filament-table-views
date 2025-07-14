@props([
    'tableView',
    'key',
    'isActive' => false,
    'actions' => []
])

@php
    use Filament\Actions\ActionGroup;

    $label = $tableView->getLabel();
    $color = $tableView->getColor();
    $icon = $tableView->getIcon();

    $record = $tableView->getRecord();

    $actions = array_filter(
        $actions,
        static function ($action) use ($record): bool {
            if (! ($action instanceof ActionGroup) && $record !== null) {
                $action->record($record);
            }

            return $action->isVisible();
        },
    );
@endphp

<div
    {{
        $attributes
            ->class([
                "bg-gray-100 dark:bg-white/10" => $isActive,
                "flex flex-1 justify-between items-center transition duration-75 h-10 px-2 py-1 hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10 rounded-lg"
            ])
    }}
>
    <button
        class="flex flex-1 items-center gap-x-1.5 w-3/5 text-sm font-normal outline-none"
        type="button"
        wire:click="toggleActiveTableView({{ \Illuminate\Support\Js::from($key) }})"
        wire:loading.attr="disabled"
    >
        @if ($icon)
            <x-filament::icon
                :attributes="
                    \Filament\Support\prepare_inherited_attributes(
                        new \Illuminate\View\ComponentAttributeBag([
                            'icon' => $icon,
                        ])
                    )
                    ->class([
                        'h-5 w-5'
                    ])
                "
            />
        @endif

        <div class="p-0.5 truncate">
            {{ $label }}
        </div>
    </button>

    <div class="flex w-2/5 flex shrink-0 items-center gap-3">
        @foreach ($actions as $action)
            {{ $action }}
        @endforeach
    </div>
</div>

