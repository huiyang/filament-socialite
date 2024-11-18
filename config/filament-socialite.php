<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth callback middleware
    |--------------------------------------------------------------------------
    |
    | This option defines the middleware that is applied to the OAuth callback url.
    |
    */

    'middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hide
    |--------------------------------------------------------------------------
    |
    | This option defines the filament admin panels which should not show socialite login in login page.
    |
    */

    'hide_on_panel' => array_map('trim', array_filter(explode(',', env('FILAMENT_SOCIALITE_HIDE_ON_PANEL')))),
];
