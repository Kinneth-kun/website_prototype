<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_filter(explode(',', env('FRONTEND_URLS', 'http://localhost:5173,http://127.0.0.1:5173'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'Origin'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
