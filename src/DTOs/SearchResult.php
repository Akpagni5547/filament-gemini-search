<?php

namespace SelfProject\FilamentGeminiSearch\DTOs;

class SearchResult
{
    public function __construct(
        public readonly string $text,
        public readonly array $sources = [],
        public readonly array $searchQueries = [],
    ) {}

    public static function fromApiResponse(array $response): self
    {
        $candidate = $response['candidates'][0] ?? [];
        $content = $candidate['content']['parts'][0]['text'] ?? '';
        $grounding = $candidate['groundingMetadata'] ?? [];

        $sources = [];
        foreach ($grounding['groundingChunks'] ?? [] as $chunk) {
            $web = $chunk['web'] ?? [];
            if (isset($web['uri'], $web['title'])) {
                $sources[] = [
                    'title' => $web['title'],
                    'uri' => $web['uri'],
                ];
            }
        }

        return new self(
            text: $content,
            sources: $sources,
            searchQueries: $grounding['webSearchQueries'] ?? [],
        );
    }
}
