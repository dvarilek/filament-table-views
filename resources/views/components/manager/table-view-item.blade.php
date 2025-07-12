@props([
    'tableView',
    'key',
    'isActive' => false,
])

@php
    $label = $tableView->getLabel();
    $color = $tableView->getColor();

    $icon = $tableView->getIcon();
@endphp

<button
    {{
        $attributes
            ->merge([
                'x-on:click' => '$wire.call(\'toggleActiveTableView\', ' . \Illuminate\Support\Js::from($key) . ')',
                'type' => 'button',
                'wire.loading.attr' => 'disabled',
                'tabindex' => '-1'
            ])
            ->class([
                'w-full'
            ])
            ->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [600],
                    alias: 'tableView',
                ),
                'border-bottom-color: rgb(var(--c-600)); border-bottom-width: 2px;' => $isActive,
                'border-bottom-color: transparent; border-bottom-width: 2px;' => ! $isActive,
            ])
    }}
>
    <div
        tabindex='0'
        class='
            fi-table-views-manager-view-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75
            outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5
            dark:focus-visible:bg-white/5 fi-color-gray h-14 text-base font-semibold leading-6 text-gray-950 dark:text-white px-2 py-1
        '
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

        <span class='flex-1 truncate text-start text-gray-700 dark:text-gray-200'>
        {{ $label }}
    </span>
    </div>
</button>
