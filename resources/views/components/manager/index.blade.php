@php
    use Filament\Support\Enums\MaxWidth;

@endphp

@props([
    'customTableViews',
    'defaultTableViews',
    'customTableViewActions',
    'activeTableViewKey'
])

@php
    $favoriteCustomTableViews = [];
    $publicCustomTableViews = [];
    $personalCustomTableViews = [];

    foreach ($customTableViews as $key => $tableView) {
        if ($tableView->isFavorite()) {
            $favoriteCustomTableViews[$key] = $tableView;
        } elseif ($tableView->isPublic()) {
            $publicCustomTableViews[$key] = $tableView;
        } else {
            $personalCustomTableViews[$key] = $tableView;
        }
    }
@endphp

<div class="flex flex-1 flex-col p-4 space-y-4">
    <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
        {{ __('filament-table-views::toolbar.actions.manage-table-views.label') }}
    </h4>

    <div class="py-2">
        Search
    </div>

    <div class="space-y-4">
        @if (filled($favoriteCustomTableViews))
            <div class="space-y-3">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    @lang('filament-table-views::toolbar.actions.manage-table-views.favorite_subheading')
                </h5>

                <div class="space-y-1">
                    @foreach($favoriteCustomTableViews as $key => $tableView)
                        <x-filament-table-views::table-view
                            :wire-key="'filament-table-views-custom-view-' . $key . '-'"
                            :tableView="$tableView"
                            :key="$key"
                            :isActive="(string) $key === $activeTableViewKey"
                            class="w-32"
                        />
                    @endforeach
                </div>
            </div>

            @if(filled($personalCustomTableViews))
                <div class="pb-4"></div>
            @endif
        @endif

        @if (filled($personalCustomTableViews))
            <div class="space-y-3">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    @lang('filament-table-views::toolbar.actions.manage-table-views.personal_subheading')
                </h5>

                <div class="space-y-1">
                    @foreach($personalCustomTableViews as $key => $tableView)
                        <x-filament-table-views::manager.table-view-item
                            :tableView="$tableView"
                            :key="$key"
                            :isActive="(string) $key === $activeTableViewKey"
                        />
                    @endforeach
                </div>
            </div>

            @if(filled($publicCustomTableViews))
                <div class="pb-4"></div>
            @endif
        @endif

        @if (filled($publicCustomTableViews))
            <div class="space-y-3">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    @lang('filament-table-views::toolbar.actions.manage-table-views.public_subheading')
                </h5>

                <div class="space-y-1">
                    @foreach($publicCustomTableViews as $key => $tableView)
                        <x-filament-table-views::manager.table-view-item
                            :tableView="$tableView"
                            :key="$key"
                            :isActive="(string) $key === $activeTableViewKey"
                        />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
