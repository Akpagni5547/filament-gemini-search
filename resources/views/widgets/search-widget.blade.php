@php $t = fn($k) => \SelfProject\FilamentGeminiSearch\trans_key($k); @endphp
<x-filament-widgets::widget>
    <x-filament::section :heading="$t('title')">
        {{-- Search bar: input + buttons on same line --}}
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
            <div style="margin-top:16px;border:1px solid rgba(0,0,0,0.08);border-radius:8px;overflow:hidden">
                <div style="padding:8px 12px;background:rgba(0,0,0,0.02);border-bottom:1px solid rgba(0,0,0,0.06)">
                    <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em">{{ $t('history_recent') }}</span>
                </div>
                @if(count($this->history) > 0)
                    <div style="max-height:192px;overflow-y:auto">
                        @foreach($this->history as $entry)
                            <button
                                wire:click="loadFromHistory({{ $entry['id'] }})"
                                style="display:flex;width:100%;justify-content:space-between;align-items:center;gap:12px;padding:10px 12px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.04);cursor:pointer;background:transparent;border:none;transition:background 0.15s"
                                onmouseover="this.style.background='rgba(0,0,0,0.02)'"
                                onmouseout="this.style.background='transparent'"
                            >
                                <span style="font-size:13px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $entry['query'] }}</span>
                                <span style="font-size:11px;color:#9ca3af;flex-shrink:0">{{ $entry['created_at'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px">{{ $t('history_empty') }}</div>
                @endif
            </div>
        @endif

        {{-- Loading --}}
        <div wire:loading wire:target="search" style="margin-top:20px;text-align:center;padding:24px 0">
            <x-filament::loading-indicator class="h-5 w-5" style="display:inline-block;margin-bottom:8px" />
            <div style="font-size:13px;color:#6b7280">{{ $t('loading_widget') }}</div>
        </div>

        {{-- Résultats --}}
        @if($this->resultText)
            <div wire:loading.remove wire:target="search" style="margin-top:20px">
                {{-- Header résultat --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981"></span>
                        <span style="font-size:13px;font-weight:600;color:#059669">{{ $t('result_gemini') }}</span>
                    </div>
                    <button wire:click="clearResult" style="font-size:12px;color:#9ca3af;cursor:pointer;background:none;border:none;padding:4px 8px;border-radius:4px" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">{{ $t('clear') }}</button>
                </div>

                {{-- Contenu --}}
                <div style="padding:20px;background:rgba(0,0,0,0.015);border:1px solid rgba(0,0,0,0.06);border-radius:10px;font-size:14px;line-height:1.7;color:#374151">
                    <style>
                        .gemini-widget-result h1,.gemini-widget-result h2,.gemini-widget-result h3 { font-weight:700;margin:16px 0 6px;color:#111827 }
                        .gemini-widget-result h1 { font-size:18px }
                        .gemini-widget-result h2 { font-size:16px }
                        .gemini-widget-result h3 { font-size:14px }
                        .gemini-widget-result p { margin:0 0 10px }
                        .gemini-widget-result ul,.gemini-widget-result ol { margin:0 0 10px;padding-left:20px }
                        .gemini-widget-result li { margin:3px 0 }
                        .gemini-widget-result strong { font-weight:700;color:#111827 }
                        .gemini-widget-result a { color:#2563eb;text-decoration:underline }
                        .gemini-widget-result code { font-size:12px;background:rgba(0,0,0,0.06);padding:2px 6px;border-radius:4px }
                        .gemini-widget-result table { width:100%;border-collapse:collapse;margin:10px 0;font-size:13px }
                        .gemini-widget-result th,.gemini-widget-result td { padding:6px 10px;border:1px solid rgba(0,0,0,0.1);text-align:left }
                        .gemini-widget-result th { background:rgba(0,0,0,0.03);font-weight:600 }
                    </style>
                    <div class="gemini-widget-result">
                        {!! \Illuminate\Support\Str::markdown($this->resultText) !!}
                    </div>
                </div>

                {{-- Sources --}}
                @if(count($this->resultSources) > 0)
                    <div style="margin-top:14px">
                        <span style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em">{{ $t('sources') }}</span>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
                            @foreach(array_slice($this->resultSources, 0, 6) as $source)
                                <a href="{{ $source['uri'] }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;font-size:12px;font-weight:500;color:#4b5563;background:rgba(0,0,0,0.03);border:1px solid rgba(0,0,0,0.08);border-radius:6px;text-decoration:none;transition:all 0.15s" onmouseover="this.style.borderColor='#3b82f6';this.style.color='#2563eb'" onmouseout="this.style.borderColor='rgba(0,0,0,0.08)';this.style.color='#4b5563'">
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
