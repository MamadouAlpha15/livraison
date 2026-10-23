            @php
                $W = 660; $H = 160;
                $pL = 52; $pR = 16; $pT = 16; $pB = 28;
                $iW = $W - $pL - $pR;
                $iH = $H - $pT - $pB;
                $n7 = count($days7);
                $pts = [];
                foreach ($days7 as $i => $day) {
                    $px = $pL + ($n7 > 1 ? ($i / ($n7 - 1)) * $iW : $iW / 2);
                    $py = $pT + $iH - ($max7 > 0 ? ($day['value'] / $max7) * $iH : 0);
                    $pts[] = ['x' => round($px,2), 'y' => round($py,2), 'v' => $day['value'], 'lbl' => $day['label'], 'today' => $day['today']];
                }
                $polyline   = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $pts));
                $areaPoints = ($pts[0]['x'].','.(  $pT+$iH).' '.$polyline.' '.$pts[$n7-1]['x'].','.(  $pT+$iH));
                $week7Total = $days7->sum('value');
                $bestDay    = $days7->sortByDesc('value')->first();
                // Y-axis labels: 0, mid, max
                $yGrid = [
                    ['val' => $max7,     'y' => $pT],
                    ['val' => $max7 / 2, 'y' => $pT + $iH / 2],
                    ['val' => 0,         'y' => $pT + $iH],
                ];
                function rcFmt($n) {
                    if ($n >= 1000000) return round($n/1000000,1).'M';
                    if ($n >= 1000)    return round($n/1000).'k';
                    return round($n);
                }
            @endphp
            <div class="plan-locked-wrap">
            @if(!$isPro)
            <div class="plan-locked-overlay">
                <div class="plan-locked-ico">📊</div>
                <div class="plan-locked-title">Graphiques — Plan Pro requis</div>
                <div class="plan-locked-sub">Les graphiques de revenus et de commandes sont réservés au Plan Pro. Passez au Pro pour visualiser l'évolution de votre activité.</div>
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les graphiques — {{ $proPriceLabel }}</a>
            </div>
            @endif
            <div class="charts-duo" style="{{ !$isPro ? 'filter:blur(3px);pointer-events:none;user-select:none' : '' }}">
            <div class="card chart-wrap" style="margin-bottom:0">
                {{-- Header --}}
                <div class="rc-header">
                    <div class="rc-header-left">
                        <div class="rc-title">{{ __('app.revenue_7days') }}</div>
                        <div class="rc-total">
                            <sup>{{ $devise }} </sup>{{ number_format($week7Total, 0, ',', ' ') }}
                        </div>
                        @php
                            $prevWeek  = $prev7Total;
                            $todayEntry = $days7->firstWhere('today', true);
                            $todayVal   = $todayEntry['value'] ?? 0;
                            $n7c        = $days7->count();
                            $yesterVal  = $n7c >= 2 ? $days7->get($n7c - 2)['value'] : 0;
                            $rcDelta    = $yesterVal > 0 ? round((($todayVal - $yesterVal) / $yesterVal) * 100, 1) : ($todayVal > 0 ? 100 : 0);
                        @endphp
                        <div id="rcDeltaBadge" class="rc-delta {{ $rcDelta > 0 ? 'up' : ($rcDelta < 0 ? 'down' : 'flat') }}">
                            @if($rcDelta > 0) ↑ +{{ $rcDelta }}% aujourd'hui vs hier
                            @elseif($rcDelta < 0) ↓ {{ $rcDelta }}% aujourd'hui vs hier
                            @else → Stable aujourd'hui
                            @endif
                        </div>
                    </div>
                    <div class="rc-header-right">
                        <div style="margin-bottom:2px">{{ __('app.best_day') }}</div>
                        <div class="rc-best">{{ $bestDay['label'] }} — {{ number_format($bestDay['value'],0,',',' ') }} {{ $devise }}</div>
                        <div style="margin-top:6px">{{ __('app.avg_per_day') }}</div>
                        <div style="font-weight:700;color:var(--text);font-size:12px">{{ number_format($week7Total/7,0,',',' ') }} {{ $devise }}</div>
                    </div>
                </div>

                {{-- SVG Chart --}}
                <div class="rc-svg-wrap">
                    <div class="rc-tooltip" id="rcTip">
                        <span id="rcTipDay"></span>
                        <strong id="rcTipVal"></strong>
                    </div>
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" height="160" preserveAspectRatio="none" overflow="visible" style="display:block">
                        <defs>
                            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"   stop-color="#1F7CF6" stop-opacity=".38"/>
                                <stop offset="45%"  stop-color="#1668D8" stop-opacity=".14"/>
                                <stop offset="100%" stop-color="#1668D8" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%"   stop-color="#1F7CF6"/>
                                <stop offset="100%" stop-color="#1668D8"/>
                            </linearGradient>
                            <filter id="glowLine" x="-10%" y="-80%" width="120%" height="260%">
                                <feGaussianBlur stdDeviation="3.5" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                            <filter id="glowDot" x="-80%" y="-80%" width="260%" height="260%">
                                <feGaussianBlur stdDeviation="4" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>

                        {{-- Y gridlines premium --}}
                        @foreach($yGrid as $g)
                        <line x1="{{ $pL }}" y1="{{ $g['y'] }}" x2="{{ $W - $pR }}" y2="{{ $g['y'] }}"
                              stroke="rgba(22,104,216,.12)" stroke-width="1" stroke-dasharray="{{ $g['val'] > 0 ? '5 6' : 'none' }}"/>
                        <text x="{{ $pL - 8 }}" y="{{ $g['y'] + 4 }}" text-anchor="end"
                              font-size="9" fill="#94a3b8" font-family="monospace">{{ rcFmt($g['val']) }}</text>
                        @endforeach

                        {{-- Area fill --}}
                        <polygon points="{{ $areaPoints }}" fill="url(#areaGrad)"/>

                        {{-- Glow line shadow --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="rgba(31,124,246,.35)" stroke-width="6" stroke-linejoin="round" stroke-linecap="round"/>
                        {{-- Line --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="url(#lineGrad)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                        {{-- Points + hit areas --}}
                        @foreach($pts as $i => $p)
                        @if($p['today'])
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="9" fill="rgba(31,124,246,.18)" stroke="none"/>
                        @endif
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $p['today'] ? 5 : 3.5 }}"
                                fill="{{ $p['today'] ? '#1F7CF6' : '#fff' }}"
                                stroke="{{ $p['today'] ? '#7DB4FF' : '#1668D8' }}" stroke-width="{{ $p['today'] ? 2.5 : 1.8 }}"
                                class="rc-pt"
                                data-val="{{ $p['v'] }}"
                                data-lbl="{{ $p['lbl'] }}"
                                data-today="{{ $p['today'] ? '1' : '0' }}"/>
                        {{-- invisible wide hit area --}}
                        <rect x="{{ $p['x'] - 20 }}" y="{{ $pT }}" width="40" height="{{ $iH }}"
                              fill="transparent"
                              class="rc-hit"
                              data-idx="{{ $i }}"/>
                        @endforeach
                    </svg>

                    {{-- X-axis day labels --}}
                    <div class="rc-day-dots" style="margin-left:{{ $pL }}px;margin-right:{{ $pR }}px">
                        @foreach($days7 as $day)
                        <div class="rc-day-dot {{ $day['today'] ? 'today' : '' }}">{{ $day['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Mini bar chart : Commandes 7j --}}
            <div class="card" style="margin-bottom:0">
                <div class="mini-chart-hd">
                    <div>
                        <div class="mini-chart-lbl">{{ __('app.orders_chart') }}</div>
                        <div class="mini-chart-val">{{ $cmdDays7->sum('count') }}</div>
                        <div class="mini-chart-sub">{{ __('app.last_7_days') }}</div>
                        @php
                            $cmdToday7  = $cmdDays7->last()['count'] ?? 0;
                            $cmdYest7   = $cmdDays7->count() >= 2 ? $cmdDays7->get($cmdDays7->count()-2)['count'] : 0;
                            $cmdDelta7  = $cmdToday7 - $cmdYest7;
                        @endphp
                        <div id="cmdDeltaBadge" class="mini-delta {{ $cmdDelta7 > 0 ? 'up' : ($cmdDelta7 < 0 ? 'down' : 'flat') }}">
                            @if($cmdDelta7 > 0) ↑ {{ $cmdToday7 }} aujourd'hui
                            @elseif($cmdDelta7 < 0) ↓ {{ $cmdToday7 }} aujourd'hui
                            @else → {{ $cmdToday7 }} aujourd'hui
                            @endif
                        </div>
                    </div>
                    <span class="mini-period-badge">30 derniers jours ▾</span>
                </div>
                <div class="mini-bars-wrap">
                    @foreach($cmdDays7 as $cd)
                    @php $hPct = $maxCmd7 > 0 ? max(round(($cd['count']/$maxCmd7)*100),2) : 2; @endphp
                    <div class="mini-bar-col">
                        <div class="mini-bar {{ $cd['today'] ? 'actuel' : '' }}"
                             data-h="{{ $hPct }}"
                             style="height:{{ $hPct }}%;transform:scaleY(0);{{ $cd['today'] ? 'opacity:1' : '' }}"></div>
                    </div>
                    @endforeach
                </div>
                <div class="mini-bar-labs">
                    @foreach($cmdDays7 as $cd)
                    <div class="mini-bar-lbl" style="flex:1;text-align:center;{{ $cd['today'] ? 'color:var(--brand);font-weight:700' : '' }}">{{ $cd['label'] }}</div>
                    @endforeach
                </div>
            </div>
            </div>{{-- /charts-duo --}}
            </div>{{-- /plan-locked-wrap charts --}}
