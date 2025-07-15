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

                'personal' => 'Osobní',

                'default' => 'Defaultní',

            ],

            'sections' => [

                'favorite' => 'Oblíbené',

                'public' => 'Veřejné',

                'personal' => 'Osobní',

                'default' => 'Defaultní',

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

    ],

];
