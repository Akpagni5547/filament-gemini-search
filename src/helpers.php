<?php

namespace SelfProject\FilamentGeminiSearch;

function trans_key(string $key): string
{
    $locale = config('gemini-search.locale') ?? app()->getLocale();

    // Fallback to 'en' if locale not supported
    if (! in_array($locale, ['fr', 'en'])) {
        $locale = 'en';
    }

    return __("gemini-search::search.{$key}", [], $locale);
}
