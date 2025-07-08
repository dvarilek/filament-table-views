@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\IconPosition;
@endphp

@props([
    'tableView',
    'key',
    'iconPosition' => null,
    'isActive' => false
])

@php
    $label = $tableView->getLabel();
    $tooltip = $tableView->getTooltip();
    $color = $tableView->getColor();

    $icon = $tableView->getIcon();
@endphp

<button
    {{
        $attributes
            ->merge([
                'x-on:click' => '$wire.call(\'toggleActiveTableView\', ' . \Illuminate\Support\Js::from($key) . ')',
                'x-tooltip' => filled($tooltip)
                    ? '{
                        content: ' . \Illuminate\Support\Js::from($tooltip) . ',
                        theme: $store.theme
                    }'
                    : null,
                'tabindex' => '-1'
            ], false)
            ->class([
                 'fi-table-views-view-item min-w-[theme(spacing.5)] p-1'
            ])
            // TODO: Handle the border using tailwind, couldn't get it to work
            ->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [600],
                    alias: 'tableView',
                ),
                "border-bottom-color: rgb(var(--c-600)); border-bottom-width: 2px;" => $isActive,
                "border-bottom-color: transparent; border-bottom-width: 2px;" => ! $isActive,
            ])
    }}
>
    <div
        tabindex="0"
        class="
            fi-table-views-view-item-inner flex items-center gap-x-1.5 transition duration-75 min-h-8 px-2 py-1 text-md font-normal
            hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10
            dark:focus-visible:bg-white/10 rounded-lg outline-none
        "
    >
        @if ($icon && $iconPosition === IconPosition::Before)
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

        <span class="p-0.5 truncate">
            {{ $label }}
        </span>

        @if ($icon && $iconPosition === IconPosition::After)
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
    </div>
</button>
