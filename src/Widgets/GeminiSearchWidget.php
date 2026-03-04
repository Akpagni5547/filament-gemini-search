<?php

namespace SelfProject\FilamentGeminiSearch\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use SelfProject\FilamentGeminiSearch\Models\SearchHistory;
use SelfProject\FilamentGeminiSearch\Services\GeminiSearchService;

class GeminiSearchWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gemini-search::widgets.search-widget';

    protected int | string | array $columnSpan = 'full';

    public string $query = '';
    public ?string $resultText = null;
    public array $resultSources = [];
    public bool $showHistory = false;
    public array $history = [];

    public function search(): void
    {
        $this->validate(['query' => 'required|string|min:3']);
        $this->showHistory = false;

        try {
            $service = app(GeminiSearchService::class);
            $result = $service->search($this->query);

            $this->resultText = $result->text;
            $this->resultSources = $result->sources;

            if (config('gemini-search.history.enabled')) {
                SearchHistory::create([
                    'query' => $this->query,
                    'result_text' => $result->text,
                    'sources' => $result->sources,
                    'search_queries' => $result->searchQueries,
                    'user_id' => auth()->id(),
                ]);
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title(\SelfProject\FilamentGeminiSearch\trans_key('error_title_short'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;

        if ($this->showHistory) {
            $this->history = SearchHistory::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($h) => [
                    'id' => $h->id,
                    'query' => $h->query,
                    'created_at' => $h->created_at->diffForHumans(),
                ])
                ->toArray();
        }
    }

    public function loadFromHistory(int $id): void
    {
        $entry = SearchHistory::find($id);
        if (! $entry) return;

        $this->query = $entry->query;
        $this->resultText = $entry->result_text;
        $this->resultSources = $entry->sources ?? [];
        $this->showHistory = false;
    }

    public function clearResult(): void
    {
        $this->resultText = null;
        $this->resultSources = [];
    }
}
