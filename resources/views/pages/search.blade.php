@php $t = fn($k) => \SelfProject\FilamentGeminiSearch\trans_key($k); @endphp
<x-filament-panels::page>
    <div style="max-width:56rem;margin:0 auto">

        {{-- Barre de recherche --}}
        <x-filament::section>
            <div style="display:flex;gap:8px;align-items:stretch">
                <div style="flex:1">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="query"
                            :placeholder="$t('placeholder')"
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
        </x-filament::section>

        {{-- Panneau Historique --}}
        @if($this->showHistory)
            <div style="margin-top:16px">
                <x-filament::section>
                    <x-slot name="heading">
                        {{ $t('history_title') }}
                    </x-slot>
                    <x-slot name="headerEnd">
                        <x-filament::button size="xs" color="gray" wire:click="toggleHistory">{{ $t('history_close') }}</x-filament::button>
                    </x-slot>

                    @if(count($this->history) > 0)
                        <div style="max-height:320px;overflow-y:auto">
                            @foreach($this->history as $entry)
                                <button
                                    wire:click="loadFromHistory({{ $entry['id'] }})"
                                    style="display:flex;width:100%;justify-content:space-between;align-items:center;gap:16px;padding:12px 16px;text-align:left;border-bottom:1px solid rgba(0,0,0,0.06);cursor:pointer;background:transparent;border:none;transition:background 0.15s"
                                    onmouseover="this.style.background='rgba(0,0,0,0.03)'"
                                    onmouseout="this.style.background='transparent'"
                                >
                                    <span style="font-size:14px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $entry['query'] }}</span>
                                    <span style="font-size:12px;color:#9ca3af;flex-shrink:0">{{ $entry['created_at'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div style="padding:24px;text-align:center;color:#9ca3af;font-size:14px">{{ $t('history_empty') }}</div>
                    @endif
                </x-filament::section>
            </div>
        @endif

        {{-- Loading --}}
        <div wire:loading wire:target="search" style="margin-top:20px">
            <x-filament::section>
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:32px 0">
                    <x-filament::loading-indicator class="h-6 w-6" style="display:inline-block" />
                    <div>
                        <div style="font-size:14px;font-weight:500;color:#374151">{{ $t('loading_title') }}</div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px">{{ $t('loading_subtitle') }}</div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Résultats --}}
        @if($this->resultText)
            <div wire:loading.remove wire:target="search">

                {{-- Résultat principal --}}
                <div style="margin-top:20px">
                    <x-filament::section>
                        <x-slot name="heading">
                            <span style="display:inline-flex;align-items:center;gap:8px">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10b981"></span>
                                {{ $t('result') }}
                            </span>
                        </x-slot>
                        <x-slot name="headerEnd">
                            <x-filament::button size="xs" color="danger" outlined wire:click="clearResult" icon="heroicon-m-trash">
                                {{ $t('clear') }}
                            </x-filament::button>
                        </x-slot>

                        <div style="font-size:14px;line-height:1.8;color:#374151;padding:8px 0">
                            <style>
                                .gemini-result h1,.gemini-result h2,.gemini-result h3,.gemini-result h4 { font-weight:700;margin:20px 0 8px;color:#111827 }
                                .gemini-result h1 { font-size:20px }
                                .gemini-result h2 { font-size:17px }
                                .gemini-result h3 { font-size:15px }
                                .gemini-result p { margin:0 0 12px }
                                .gemini-result ul,.gemini-result ol { margin:0 0 12px;padding-left:24px }
                                .gemini-result li { margin:4px 0 }
                                .gemini-result strong { font-weight:700;color:#111827 }
                                .gemini-result a { color:#2563eb;text-decoration:underline }
                                .gemini-result a:hover { color:#1d4ed8 }
                                .gemini-result code { font-size:12px;background:rgba(0,0,0,0.06);padding:2px 6px;border-radius:4px }
                                .gemini-result blockquote { border-left:3px solid #d1d5db;padding:4px 16px;margin:12px 0;color:#6b7280 }
                                .gemini-result table { width:100%;border-collapse:collapse;margin:12px 0;font-size:13px }
                                .gemini-result th,.gemini-result td { padding:8px 12px;border:1px solid rgba(0,0,0,0.1);text-align:left }
                                .gemini-result th { background:rgba(0,0,0,0.03);font-weight:600 }
                                .gemini-result hr { border:none;border-top:1px solid rgba(0,0,0,0.1);margin:16px 0 }
                            </style>
                            <div class="gemini-result">
                                {!! \Illuminate\Support\Str::markdown($this->resultText) !!}
                            </div>
                        </div>
                    </x-filament::section>
                </div>

                {{-- Sources --}}
                @if(count($this->resultSources) > 0)
                    <div style="margin-top:12px">
                        <x-filament::section collapsible>
                            <x-slot name="heading">
                                {{ trans_choice('gemini-search::search.source_count', count($this->resultSources), ['count' => count($this->resultSources)]) }}
                            </x-slot>

                            <div style="display:flex;flex-wrap:wrap;gap:8px">
                                @foreach($this->resultSources as $source)
                                    <a href="{{ $source['uri'] }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;font-size:13px;font-weight:500;color:#4b5563;background:rgba(0,0,0,0.03);border:1px solid rgba(0,0,0,0.1);border-radius:8px;text-decoration:none;transition:all 0.15s" onmouseover="this.style.borderColor='#3b82f6';this.style.color='#2563eb'" onmouseout="this.style.borderColor='rgba(0,0,0,0.1)';this.style.color='#4b5563'">
                                        {{ $source['title'] }} ↗
                                    </a>
                                @endforeach
                            </div>
                        </x-filament::section>
                    </div>
                @endif

                {{-- Requêtes Google --}}
                @if(count($this->resultSearchQueries) > 0)
                    <div style="margin-top:12px;padding:0 4px">
                        <div style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px">{{ $t('search_queries') }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            @foreach($this->resultSearchQueries as $sq)
                                <span style="padding:4px 10px;font-size:12px;color:#6b7280;background:rgba(0,0,0,0.04);border-radius:6px">{{ $sq }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- État vide --}}
        @if(!$this->resultText && !$this->showHistory)
            <div wire:loading.remove wire:target="search" style="margin-top:20px">
                <x-filament::section>
                    <div style="text-align:center;padding:24px 0">
                        <div style="font-size:14px;color:#6b7280;margin-bottom:16px">
                            {{ $t('empty_title') }}
                        </div>
                        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px">
                            @foreach(['Prix climatiseur Nasco 2CV Abidjan', 'Forfaits internet Orange CI', 'Créer une SARL en Côte d\'Ivoire'] as $suggestion)
                                <button wire:click="$set('query', '{{ addslashes($suggestion) }}')" style="padding:6px 14px;font-size:13px;color:#4b5563;background:rgba(0,0,0,0.03);border:1px solid rgba(0,0,0,0.1);border-radius:8px;cursor:pointer;transition:all 0.15s" onmouseover="this.style.borderColor='#3b82f6';this.style.color='#2563eb'" onmouseout="this.style.borderColor='rgba(0,0,0,0.1)';this.style.color='#4b5563'">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </x-filament::section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
