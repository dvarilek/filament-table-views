<?php

declare(strict_types=1);

return [

    'actions' => [

        'table-view-action' => [

            'form' => [

                'name' => 'Name',

                'icon' => 'Icon',

                'color' => 'Color',

                'is_public' => 'Is public',

                'is_favorite' => 'Is favorite',

                'is_globally_highlighted' => 'Is globally highlighted',

                'description' => 'Description',

            ],

        ],

        'create-table-view' => [

            'label' => 'Save view',

            'description' => 'Save your prefiltered table view for future use',

            'submit_label' => 'Create view',

            'notifications' => [

                'after_table_view_created' => [

                    'title' => 'Table view created',

                ],

            ],

        ],

        'manage-table-views' => [

            'label' => 'Manage table views',

            'search' => [

                'label' => 'Search',

                'placeholder' => 'Search',

            ],

            'filters' => [

                'favorite' => 'Favorites',

                'public' => 'Public',

                'personal' => 'Personal',

                'default' => 'Default'

            ],

            'sections' => [

                'favorite' => 'Favorites',

                'public' => 'Public',

                'personal' => 'Personal',

                'default' => 'Default'

            ],

            'empty-state' => [

                'search_empty_state' => 'No matching table views found',

                'no_views_empty_state' => 'No table views available'

            ]

        ],

        'edit-table-view' => [

            'label' => 'Edit view',

            'submit_label' => 'Update view',

            'form' => [

                'should_update_view' => 'Update view filters',

            ],

            'notifications' => [

                'after_table_view_updated' => [

                    'title' => 'Table view updated',

                ],

            ],

        ],

    ],

];
