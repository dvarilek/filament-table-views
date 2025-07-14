<?php

return [

    'custom_table_view_model' => [

        /**
         * Indicates whether the table view's color attribute is stored as JSON.
         */
        'color_attribute_is_json' => false,

        /**
         * The database table used to store user table views.
         */
        'table' => 'custom_table_views',

    ],

    'table_views' => [

        /**
         * Determines whether the active table view should be persistent in the user's session globally.
         */
        'persists_active_table_view_in_session' => false,

    ],

];
