<?php
return [
    'paths' => ['api/*', 'register', 'login', 'user', 'logout', 'refresh','broadcasting/auth',],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5500'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', '*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
