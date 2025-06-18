<?php

return [
    'database' => [
        'prefix_length'    => 3,
        'prefix_separator' => '-',

        'suffix_length'    => 3,
        'suffix_separator' => '-',

        'default_profile_is_active' => false,

        'tenant_id_column' => 'tenant_id',
    ],
];
