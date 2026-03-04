<?php

namespace SelfProject\FilamentGeminiSearch;

use Illuminate\Support\ServiceProvider;
use SelfProject\FilamentGeminiSearch\Services\GeminiSearchService;

class FilamentGeminiSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gemini-search.php', 'gemini-search');

        $this->app->singleton(GeminiSearchService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gemini-search');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'gemini-search');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/gemini-search.php' => config_path('gemini-search.php'),
            ], 'gemini-search-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/gemini-search'),
            ], 'gemini-search-views');

            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/gemini-search'),
            ], 'gemini-search-lang');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
