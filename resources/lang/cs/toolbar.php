<?php

declare(strict_types=1);

return [

    'actions' => [

        'table-view-action' => [

            'form' => [

                'name' => 'Název',

                'icon' => 'Ikona',

                'color' => 'Barva',

                'is_public' => 'Je veřejný',

                'is_favorite' => 'Je oblíbený',

                'is_default' => 'Je výchozí',

                'description' => 'Popis',

            ],

        ],

        'create-table-view' => [

            'label' => 'Uložit pohled',

            'description' => 'Uložte si váš předfiltrovaný pohled tabulky pro budoucí použití',

            'submit_label' => 'Vytvořit pohled',

            'notifications' => [

                'after_table_view_created' => [

                    'title' => 'Pohled tabulky vytvořen',

                ],

            ],

        ],

        'manage-table-views' => [

            'label' => 'Správa pohledů',

            'reset_label' => 'Reset',

            'search' => [

                'label' => 'Hledat',

                'placeholder' => 'Hledat',

            ],

            'filters' => [

                'favorite' => 'Oblíbené',

                'public' => 'Veřejné',

                'private' => 'Soukromé',

                'system' => 'Systémové',

            ],

            'groups' => [

                'favorite' => 'Oblíbené',

                'public' => 'Veřejné',

                'private' => 'Soukromé',

                'system' => 'Systémové',

            ],

            'group_item_badges' => [

                'public' => 'Veřejné',

                'private' => 'Soukromé',

                'default' => 'Výchozí',
                
            ],

            'reordering' => [

                'confirm_reordering' => 'Uložit pořadí',

                'loading_indicator' => 'Ukládám...',

                'stop_reordering' => 'Zrušit',

                'reordered_notification_title' => 'Uloženo',

                'multi_group_reorder_heading' => 'Změnit pořadí pohledů',

            ],

            'empty-state' => [

                'search_empty_state' => 'Nenalezeny žádné odpovídající pohledy',

                'no_views_empty_state' => 'Nejsou k dispozici žádné pohledy',

            ],

        ],

        'edit-table-view' => [

            'label' => 'Upravit pohled',

            'submit_label' => 'Aktualizovat pohled',

            'form' => [

                'should_update_view' => 'Upravit filtry pohledu tabulky',

            ],

            'notifications' => [

                'after_table_view_updated' => [

                    'title' => 'Pohled tabulky aktualizován',

                ],

            ],

        ],

        'toggle-public-table-view' => [

            'make_private_label' => 'Nastavit jako soukromé',

            'make_public_label' => 'Nastavit jako veřejné',

        ],

        'toggle-favorite-table-view' => [

            'remove_favorite_label' => 'Odstranit z oblíbených',

            'make_favorite_label' => 'Přidat do oblíbených',

            'remove_default_label' => 'Zrušit jako výchozí',

            'make_default_label' => 'Nastavit jako výchozí',

        ],

        'delete-table-view' => [

            'label' => 'Odstranit pohled',

        ],

    ],

];
