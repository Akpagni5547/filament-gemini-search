<?php

namespace SelfProject\FilamentGeminiSearch;

use Filament\Contracts\Plugin;
use Filament\Panel;
use SelfProject\FilamentGeminiSearch\Pages\GeminiSearchPage;
use SelfProject\FilamentGeminiSearch\Widgets\GeminiSearchWidget;

class FilamentGeminiSearchPlugin implements Plugin
{
    protected ?bool $hasPage = null;
    protected ?bool $hasWidget = null;

    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'gemini-search';
    }

    public function page(bool $enabled = true): static
    {
        $this->hasPage = $enabled;
        return $this;
    }

    public function widget(bool $enabled = true): static
    {
        $this->hasWidget = $enabled;
        return $this;
    }

    public function disablePage(): static
    {
        return $this->page(false);
    }

    public function disableWidget(): static
    {
        return $this->widget(false);
    }

    public function register(Panel $panel): void
    {
        $pageEnabled = $this->hasPage ?? config('gemini-search.page.enabled', true);
        $widgetEnabled = $this->hasWidget ?? config('gemini-search.widget.enabled', true);

        if ($pageEnabled) {
            $panel->pages([GeminiSearchPage::class]);
        }

        if ($widgetEnabled) {
            $panel->widgets([GeminiSearchWidget::class]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
