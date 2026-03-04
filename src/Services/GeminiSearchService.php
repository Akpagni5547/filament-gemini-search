<?php

namespace SelfProject\FilamentGeminiSearch\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use SelfProject\FilamentGeminiSearch\DTOs\SearchResult;

class GeminiSearchService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected bool $groundingEnabled;

    public function __construct()
    {
        $this->apiKey = config('gemini-search.api_key');
        $this->model = config('gemini-search.model');
        $this->baseUrl = config('gemini-search.api_base_url');
        $this->groundingEnabled = config('gemini-search.grounding.enabled', true);
    }

    public function search(string $query): SearchResult
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $query],
                    ],
                ],
            ],
        ];

        if ($this->groundingEnabled) {
            $payload['tools'] = [
                ['google_search' => new \stdClass()],
            ];
        }

        $response = $this->http()
            ->post($url, $payload)
            ->throw()
            ->json();

        return SearchResult::fromApiResponse($response);
    }

    protected function http(): PendingRequest
    {
        return Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])->timeout(30);
    }
}
