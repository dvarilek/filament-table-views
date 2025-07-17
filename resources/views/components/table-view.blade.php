@props([
    'key',
    'tableView',
    'isActive' => false,
])

@php
    use Illuminate\View\ComponentAttributeBag;

    $label = $tableView->getLabel();
    $tooltip = $tableView->getTooltip();
    $color = $tableView->getColor();
    $icon = $tableView->getIcon();
    $isDisabled = $tableView->isdisabled();
@endphp

<button
    {{
        $attributes
            ->merge([
                'x-on:click' => '$wire.call(\'toggleActiveTableView\', ' . Js::from($key) . ')',
                'disabled' => $isDisabled,
                'wire:loading.attr' => 'disabled',
                'x-tooltip' => filled($tooltip) ? '{
        content: ' . Js::from($tooltip) . ',
        theme: $store.theme
        }' :
                null,
                'tabindex' => '-1',
            ], false)
            ->class([
                // rounded corners on the bottom would be nice
                'px-2 pt-2 pb-1',
                'rounded-t-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 relative' => $isActive,
                'disabled:opacity-70' => $isDisabled,
            ])
            ->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [600],
                    alias: 'tableView',
                ),
            ])
    }}
>
    <div
        tabindex="0"
        @class([
            'flex items-center gap-x-1.5 rounded-lg px-2 py-1 text-sm font-normal outline-none',
            'transition duration-75 hover:bg-gray-100 focus:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 dark:focus-visible:bg-white/10' => ! $isDisabled,
        ])
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

        <span class="truncate p-0.5" style="max-width: 16rem">
            {{ $label }}
        </span>
    </div>
</button>
