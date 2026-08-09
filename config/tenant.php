<?php

return [
    'name'     => env('TENANT_NAME', 'New Shoes Ltd'),
    'monogram' => env('TENANT_MONOGRAM', strtoupper(substr(env('TENANT_NAME', 'New Shoes Ltd'), 0, 1))),
];
