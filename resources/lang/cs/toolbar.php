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

                'is_globally_highlighted' => 'Je globálně zvýrazněný',

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

        'edit-table-view' => [

            'label' => 'Upravit pohled',

            'submit_label' => 'Aktualizovat pohled',

            'form' => [

                'should_update_view' => 'Upravit filtry pohledu tabulky'

            ],

            'notifications' => [

                'after_table_view_updated' => [

                    'title' => 'Pohled tabulky aktualizován',

                ],

            ],

        ],

    ],

];
