@php $t = fn($k) => \SelfProject\FilamentGeminiSearch\trans_key($k); @endphp
<style>
    .gsw-text { color:#374151 } .dark .gsw-text { color:#d1d5db }
    .gsw-text-muted { color:#6b7280 } .dark .gsw-text-muted { color:#9ca3af }
    .gsw-text-faint { color:#9ca3af } .dark .gsw-text-faint { color:#6b7280 }
    .gsw-text-green { color:#059669 } .dark .gsw-text-green { color:#34d399 }
    .gsw-history-box { border:1px solid rgba(0,0,0,0.08);border-radius:8px;overflow:hidden }
    .dark .gsw-history-box { border-color:rgba(255,255,255,0.1) }
    .gsw-history-header { background:rgba(0,0,0,0.02);border-bottom:1px solid rgba(0,0,0,0.06) }
    .dark .gsw-history-header { background:rgba(255,255,255,0.03);border-bottom-color:rgba(255,255,255,0.08) }
    .gsw-history-btn { background:transparent;border:none;border-bottom:1px solid rgba(0,0,0,0.04) }
    .gsw-history-btn:hover { background:rgba(0,0,0,0.02) }
    .dark .gsw-history-btn { border-bottom-color:rgba(255,255,255,0.06) }
    .dark .gsw-history-btn:hover { background:rgba(255,255,255,0.05) }
    .gsw-result-card { padding:20px;background:rgba(0,0,0,0.015);border:1px solid rgba(0,0,0,0.06);border-radius:10px }
    .dark .gsw-result-card { background:rgba(255,255,255,0.03);border-color:rgba(255,255,255,0.08) }
    .gsw-clear-btn { color:#9ca3af;background:none;border:none;cursor:pointer } .gsw-clear-btn:hover { color:#ef4444 }
    .gsw-source-link { color:#4b5563;background:rgba(0,0,0,0.03);border:1px solid rgba(0,0,0,0.08);text-decoration:none }
    .gsw-source-link:hover { border-color:#3b82f6;color:#2563eb }
    .dark .gsw-source-link { color:#d1d5db;background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1) }
    .dark .gsw-source-link:hover { border-color:#60a5fa;color:#60a5fa }
    .gsw-result h1,.gsw-result h2,.gsw-result h3 { font-weight:700;margin:16px 0 6px }
    .gsw-result h1 { font-size:18px } .gsw-result h2 { font-size:16px } .gsw-result h3 { font-size:14px }
    .gsw-result p { margin:0 0 10px } .gsw-result ul,.gsw-result ol { margin:0 0 10px;padding-left:20px }
    .gsw-result li { margin:3px 0 } .gsw-result strong { font-weight:700 }
    .gsw-result a { color:#2563eb;text-decoration:underline } .dark .gsw-result a { color:#60a5fa }
    .gsw-result code { font-size:12px;background:rgba(0,0,0,0.06);padding:2px 6px;border-radius:4px }
    .dark .gsw-result code { background:rgba(255,255,255,0.1) }
    .gsw-result table { width:100%;border-collapse:collapse;margin:10px 0;font-size:13px }
    .gsw-result th,.gsw-result td { padding:6px 10px;border:1px solid rgba(0,0,0,0.1);text-align:left }
    .dark .gsw-result th,.dark .gsw-result td { border-color:rgba(255,255,255,0.1) }
    .gsw-result th { background:rgba(0,0,0,0.03);font-weight:600 } .dark .gsw-result th { background:rgba(255,255,255,0.05) }
</style>
<x-filament-widgets::widget>
    <x-filament::section :heading="$t('title')">
        {{-- Search bar --}}
        <div style="display:flex;gap:8px;align-items:stretch">
            <div style="flex:1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="query"
                        :placeholder="$t('placeholder_widget')"
                    />
                </x-filament::input.wrapper>
            </div>
            <x-filament::button type="button" wire:click="search" wire:loading.attr="disabled" icon="heroicon-m-magnifying-glass">
                <span wire:loading.remove wire:target="search">{{ $t('search') }}</span>
                <span wire:loading wire:target="search">{{ $t('searching') }}</span>
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="toggleHistory" icon="heroicon-m-clock">
                {{ $t('history') }}
            </x-filament::button>
        </div>

        {{-- Historique --}}
        @if($this->showHistory)
            <div class="gsw-history-box" style="margin-top:16px">
                <div class="gsw-history-header" style="padding:8px 12px">
                    <span class="gsw-text-muted" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">{{ $t('history_recent') }}</span>
                </div>
                @if(count($this->history) > 0)
                    <div style="max-height:192px;overflow-y:auto">
                        @foreach($this->history as $entry)
                            <button
                                wire:click="loadFromHistory({{ $entry['id'] }})"
                                class="gsw-history-btn"
                                style="display:flex;width:100%;justify-content:space-between;align-items:center;gap:12px;padding:10px 12px;text-align:left;cursor:pointer;transition:background 0.15s"
                            >
                                <span class="gsw-text" style="font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $entry['query'] }}</span>
                                <span class="gsw-text-faint" style="font-size:11px;flex-shrink:0">{{ $entry['created_at'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="gsw-text-faint" style="padding:20px;text-align:center;font-size:13px">{{ $t('history_empty') }}</div>
                @endif
            </div>
        @endif

        {{-- Loading --}}
        <div wire:loading wire:target="search" style="margin-top:20px;text-align:center;padding:24px 0">
            <x-filament::loading-indicator class="h-5 w-5" style="display:inline-block;margin-bottom:8px" />
            <div class="gsw-text-muted" style="font-size:13px">{{ $t('loading_widget') }}</div>
        </div>

        {{-- Résultats --}}
        @if($this->resultText)
            <div wire:loading.remove wire:target="search" style="margin-top:20px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981"></span>
                        <span class="gsw-text-green" style="font-size:13px;font-weight:600">{{ $t('result_gemini') }}</span>
                    </div>
                    <button wire:click="clearResult" class="gsw-clear-btn" style="font-size:12px;padding:4px 8px;border-radius:4px">{{ $t('clear') }}</button>
                </div>

                <div class="gsw-result-card gsw-text gsw-result" style="font-size:14px;line-height:1.7">
                    {!! \Illuminate\Support\Str::markdown($this->resultText) !!}
                </div>

                @if(count($this->resultSources) > 0)
                    <div style="margin-top:14px">
                        <span class="gsw-text-faint" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">{{ $t('sources') }}</span>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
                            @foreach(array_slice($this->resultSources, 0, 6) as $source)
                                <a href="{{ $source['uri'] }}" target="_blank" rel="noopener" class="gsw-source-link" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;font-size:12px;font-weight:500;border-radius:6px;transition:all 0.15s">
                                    {{ $source['title'] }} ↗
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
