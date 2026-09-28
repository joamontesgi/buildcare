<?php

$defaults = [
    'http://localhost:5173',
    'http://localhost:5174',
    'https://frontend-buildcare.vercel.app',
    'https://frontend-buildcare-git-main-joamontesgis-projects.vercel.app',
    'https://frontend-buildcare-p4cig107j-joamontesgis-projects.vercel.app',
];

$fromEnv = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('FRONTEND_URL', '')),
)));

return [

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique([...$defaults, ...$fromEnv])),

    'allowed_origins_patterns' => [
        '#^https://frontend-buildcare[a-z0-9-]*\.vercel\.app$#',
        '#^https://[a-z0-9-]+-joamontesgis-projects\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
