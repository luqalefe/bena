<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Bypass de desenvolvimento
    |--------------------------------------------------------------------------
    |
    | Quando true E APP_ENV != 'production', requisições sem headers Authelia
    | são autenticadas como o usuário simulado abaixo. Conveniência pra
    | iterar em features sem fluxo SSO; em produção é ignorado por design.
    */

    'dev_bypass' => env('AUTHELIA_DEV_BYPASS', false),

    'dev_user' => env('AUTHELIA_DEV_USER', 'lucas.dev'),
    'dev_groups' => env('AUTHELIA_DEV_GROUPS', 'estagiarios'),
    'dev_name' => env('AUTHELIA_DEV_NAME', 'Lucas Dev'),
    'dev_email' => env('AUTHELIA_DEV_EMAIL', 'lucas.dev@example.local'),
];
