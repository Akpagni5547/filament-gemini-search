<?php

namespace SelfProject\FilamentGeminiSearch\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use SelfProject\FilamentGeminiSearch\Models\SearchHistory;
use SelfProject\FilamentGeminiSearch\Services\GeminiSearchService;

class GeminiSearchPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gemini-search::pages.search';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return \SelfProject\FilamentGeminiSearch\trans_key('title');
    }

    public static function getNavigationLabel(): string
    {
        return \SelfProject\FilamentGeminiSearch\trans_key('title');
    }

    protected static ?string $slug = 'gemini-search';

    public string $query = '';
    public ?string $resultText = null;
    public array $resultSources = [];
    public array $resultSearchQueries = [];
    public bool $showHistory = false;
    public array $history = [];

    public static function getNavigationGroup(): ?string
    {
        return config('gemini-search.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return config('gemini-search.navigation.sort');
    }

    public function search(): void
    {
        $this->validate(['query' => 'required|string|min:3']);
        $this->showHistory = false;

        try {
            $service = app(GeminiSearchService::class);
            $result = $service->search($this->query);

            $this->resultText = $result->text;
            $this->resultSources = $result->sources;
            $this->resultSearchQueries = $result->searchQueries;

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
                ->title(\SelfProject\FilamentGeminiSearch\trans_key('error_title'))
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
                ->take(20)
                ->get()
                ->map(fn ($h) => [
                    'id' => $h->id,
                    'query' => $h->query,
                    'result_text' => $h->result_text,
                    'sources' => $h->sources ?? [],
                    'search_queries' => $h->search_queries ?? [],
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
        $this->resultSearchQueries = $entry->search_queries ?? [];
        $this->showHistory = false;
    }

    public function clearResult(): void
    {
        $this->resultText = null;
        $this->resultSources = [];
        $this->resultSearchQueries = [];
    }
}
