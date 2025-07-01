@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\IconPosition;
@endphp

@props([
    'tableView',
    'key',
    'isActive' => false
])

@php
    $isActive = $this->activeTableViewKey === $key;

    $label = $tableView->getLabel();
    $tooltip = $tableView->getTooltip();

    $icon = $tableView->getIcon();
    $iconPosition = $tableView->getIconPosition();
    $iconSize = $tableView->getIconSize();
    $color = $tableView->getColor();

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-table-views-item h-5 w-5',
        match ($iconSize) {
            IconSize::Small => 'h-4 w-4',
            IconSize::Medium => 'h-5 w-5',
            IconSize::Large => 'h-6 w-6',
            default => $iconSize,
        }
    ]);
@endphp

<button
    {{
        $attributes
            ->merge([
                'x-on:click' => '$wire.call(\'toggleActiveTableView\', ' . \Illuminate\Support\Js::from($key) . ')',
                'x-tooltip' => filled($tooltip)
                    ? '{
                        content: ' . \Illuminate\Support\Js::from($tooltip) . ',
                        theme: \$store.theme
                    }'
                    : null,
            ], false)
            ->class([
                'min-w-[theme(spacing.5)] flex items-center gap-x-1.5 outline-none transition duration-75 min-h-8 rounded-lg px-2.5 py-1.5
                hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus-visible:bg-white/10
                text-sm font-medium text-gray-600 hover:text-gray-700 focus-visible:text-gray-800 dark:text-white'
            ])
            ->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [
                        600,
                    ],
                    alias: 'tableView',
                ),
            ])
    }}
>
    @if ($icon && $iconPosition === IconPosition::Before)
        <x-filament::icon
            :attributes="
                \Filament\Support\prepare_inherited_attributes(
                    new \Illuminate\View\ComponentAttributeBag([
                        'icon' => $icon,
                    ])
                )
                ->class([$iconClasses])
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
                ->class([$iconClasses])
            "
        />
    @endif
</button>
