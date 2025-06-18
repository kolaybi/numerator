<?php

return [
    'database' => [
        'prefix_length'    => (int) env('NUMERATOR_PREFIX_LENGTH', 3),
        'prefix_separator' => env('NUMERATOR_PREFIX_SEPARATOR', '-'),

        'suffix_length'    => (int) env('NUMERATOR_SUFFIX_LENGTH', 3),
        'suffix_separator' => env('NUMERATOR_SUFFIX_SEPARATOR', '-'),

        'default_profile_is_active' => (bool) env('NUMERATOR_DEFAULT_PROFILE_IS_ACTIVE', false),

        'tenant_id_column' => env('NUMERATOR_TENANT_ID_COLUMN', 'tenant_id'),
    ],
];
