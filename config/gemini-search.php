<?php

return [
    'api_key' => env('GEMINI_API_KEY'),

    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    'api_base_url' => 'https://generativelanguage.googleapis.com/v1beta',

    'locale' => env('GEMINI_SEARCH_LOCALE', null), // null = app locale, 'fr', 'en'

    'grounding' => [
        'enabled' => true,
    ],

    'history' => [
        'enabled' => true,
        'max_records' => 500,
    ],

    'page' => [
        'enabled' => env('GEMINI_SEARCH_PAGE', true),
    ],

    'widget' => [
        'enabled' => env('GEMINI_SEARCH_WIDGET', true),
    ],

    'navigation' => [
        'group' => null,
        'icon' => 'heroicon-o-magnifying-glass',
        'sort' => 100,
    ],
];
