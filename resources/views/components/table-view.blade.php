@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\IconPosition;
@endphp

@props([
    'tableView',
    'key',
])

@php
    $isActive = $this->activeTableViewKey === $key;

    $label = $tableView->getLabel();

    $icon = $tableView->getIcon();
    $iconPosition = $tableView->getIconPosition();
    $iconSize = $tableView->getIconSize();
    $color = $tableView->getColor();

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-table-views-item h-4 w-4',
        match ($iconSize) {
            IconSize::Small => 'h-4 w-4',
            IconSize::Medium => 'h-5 w-5',
            IconSize::Large => 'h-6 w-6',
            default => $iconSize,
        },
        match ($color) {
            'gray' => 'text-gray-400 dark:text-gray-500',
            default => 'text-custom-500',
        },
    ]);
@endphp

<button
    x-on:click="$wire.call('toggleActiveTableView', '{{ $key }}')"
    {{
        $tableView
            ->getExtraAttributeBag()
            ->class([
                'fi-table-view px-1.5 min-w-[theme(spacing.5)] py-0.5 flex items-center gap-x-1.5'
            ])
    }}
>
    @if ($iconPosition === IconPosition::Before)
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

    <span class="truncate">
        {{ $label }}
    </span>

    @if ($iconPosition === IconPosition::After)
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
