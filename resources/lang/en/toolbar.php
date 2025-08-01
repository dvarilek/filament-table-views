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

                'is_default' => 'Is default',

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

            'reset_label' => 'Reset',

            'search' => [

                'label' => 'Search',

                'placeholder' => 'Search',

            ],

            'filters' => [

                'favorite' => 'Favorite',

                'public' => 'Public',

                'private' => 'Private',

                'system' => 'System',

            ],

            'groups' => [

                'favorite' => 'Favorite',

                'public' => 'Public',

                'private' => 'Private',

                'system' => 'System',

            ],

            'group_item_badges' => [

                'public' => 'Public',

                'private' => 'Private',

                'default' => 'Default',

            ],

            'reordering' => [

                'confirm_reordering' => 'Save order',

                'loading_indicator' => 'Saving...',

                'stop_reordering' => 'Cancel',

                'reordered_notification_title' => 'Saved',

                'multi_group_reorder_heading' => 'Reorder table views',

            ],

            'empty-state' => [

                'search_empty_state' => 'No matching table views found',

                'no_views_empty_state' => 'No table views available',

            ],

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

        'toggle-public-table-view' => [

            'make_private_label' => 'Make private',

            'make_public_label' => 'Make public',

        ],

        'toggle-favorite-table-view' => [

            'remove_favorite_label' => 'Remove from favorites',

            'make_favorite_label' => 'Add to favorites',

            'remove_default_label' => 'Remove as default',

            'make_default_label' => 'Set as default',

        ],

        'delete-table-view' => [

            'label' => 'Delete view',

        ],

    ],

];
