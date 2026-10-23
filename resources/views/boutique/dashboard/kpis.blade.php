            <div class="kpi-grid" style="margin-bottom:22px">
                <div class="kpi"  style="--kpi-color:#1F7CF6;--kpi-bg:#EAF2FF">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#1F7CF6,#1260C9);border-color:rgba(31,124,246,.3);box-shadow:0 0 0 3px rgba(31,124,246,.12),0 4px 14px rgba(31,124,246,.4)">{!! $I['dollar_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.net_revenue') }}</div>
                    <div class="kpi-val" id="kpiCaVal">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} · {{ $_monthYear($now) }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}" id="kpiCaDelta">{{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent</div>
                    @if($commissionsPaieesMonth > 0)
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px">
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>CA brut</span><span style="font-family:var(--mono);color:var(--text-2)">{{ number_format($caGrossMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>Commissions</span><span style="font-family:var(--mono);color:#dc2626">− {{ number_format($commissionsPaieesMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;padding-top:3px;border-top:1px dashed var(--border)"><span style="color:var(--brand)">Net</span><span style="font-family:var(--mono);color:var(--brand)">{{ number_format($caMonth,0,',',' ') }}</span></div>
                    </div>
                    @endif
                </div>
                @php
                    $_cmdColor  = !$isPro && $cmdClass === 'danger' ? '#dc2626' : (!$isPro && $cmdClass === 'warn' ? '#d97706' : '#1F7CF6');
                    $_cmdBg     = !$isPro && $cmdClass === 'danger' ? '#fef2f2' : (!$isPro && $cmdClass === 'warn' ? '#fffbeb' : '#EAF2FF');
                    $_cmdGrad   = !$isPro && $cmdClass === 'danger' ? '#ef4444,#b91c1c' : (!$isPro && $cmdClass === 'warn' ? '#f59e0b,#b45309' : '#1F7CF6,#1260C9');
                    $_cmdShadow = !$isPro && $cmdClass === 'danger' ? '0 0 0 3px rgba(220,38,38,.12),0 4px 14px rgba(220,38,38,.35)' : (!$isPro && $cmdClass === 'warn' ? '0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(217,119,6,.35)' : '0 0 0 3px rgba(31,124,246,.12),0 4px 14px rgba(31,124,246,.4)');
                    $_cmdBorder = !$isPro && $cmdClass === 'danger' ? 'border:2px solid #fca5a5;' : (!$isPro && $cmdClass === 'warn' ? 'border:2px solid #fde68a;' : '');
                @endphp
                <div class="kpi" style="--kpi-color:{{ $_cmdColor }};--kpi-bg:{{ $_cmdBg }};{{ $_cmdBorder }}">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,{{ $_cmdGrad }});border-color:rgba(31,124,246,.3);box-shadow:{{ $_cmdShadow }}">{!! $I['box_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.orders_this_month') }}</div>
                    <div class="kpi-val" id="kpiCmdVal">
                        @if(!$isPro)
                            {{ $cmdMonth }}<span style="font-size:16px;font-weight:600;color:var(--muted)">/{{ $maxCmdMois }}</span>
                        @else
                            {{ $cmdMonth }}
                        @endif
                    </div>
                    <div class="kpi-unit">commandes traitées</div>
                    <div class="kpi-delta {{ $cmdToday >= $cmdYest ? 'up':'down' }}" id="kpiCmdDelta">{{ $cmdToday >= $cmdYest ? '↑':'↓' }} {{ $cmdToday }} aujourd'hui</div>

                    @if(!$isPro)
                    {{-- Barre de progression --}}
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Utilisation</span>
                            <span style="font-size:10.5px;font-weight:800;font-family:var(--mono);color:{{ $_cmdColor }}">{{ $usageCmdPct }}%</span>
                        </div>
                        <div style="height:5px;border-radius:20px;background:{{ !$isPro && $cmdClass === 'danger' ? '#fee2e2' : (!$isPro && $cmdClass === 'warn' ? '#fef3c7' : '#D6E6FF') }};overflow:hidden;">
                            <div style="height:100%;border-radius:20px;width:{{ $usageCmdPct }}%;background:linear-gradient(90deg,{{ $_cmdGrad }});transition:width .4s ease;"></div>
                        </div>
                        @if($cmdClass === 'danger')
                        <div style="margin-top:8px;display:flex;align-items:center;justify-content:space-between;gap:6px;">
                            <span style="font-size:10.5px;font-weight:800;color:#dc2626;">🔒 Limite atteinte</span>
                            <a href="{{ route('boutique.subscription.upgrade') }}" style="font-size:10px;font-weight:800;color:#1668D8;background:#EAF2FF;border:1px solid #D6E6FF;padding:2px 8px;border-radius:20px;text-decoration:none;white-space:nowrap;">⚡ Plan Pro</a>
                        </div>
                        @elseif($cmdClass === 'warn')
                        <div style="margin-top:7px;font-size:10.5px;font-weight:700;color:#d97706;">⚠ Bientôt la limite — {{ $maxCmdMois - $cmdMonth }} restante{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }}</div>
                        @else
                        <div style="margin-top:7px;font-size:10.5px;color:var(--muted);">{{ $maxCmdMois - $cmdMonth }} commande{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }} restante{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }} ce mois</div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(169, 141, 110, 0.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(147, 94, 33, 0.4)">{!! $I['cart_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.avg_basket') }}</div>
                    <div class="kpi-val" id="kpiPanierVal">{{ number_format($panier,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} / commande</div>
                    <div class="kpi-delta {{ $panierDelta >= 0 ? 'up':'down' }}" id="kpiPanierDelta">{{ $panierDelta >= 0 ? '↑':'↓' }} {{ abs($panierDelta) }}% vs mois précédent</div>
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(217,119,6,.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(217,119,6,.4)">{!! $I['bike_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.delivery_rate') }}</div>
                    <div class="kpi-val" id="kpiTauxVal">{{ $tauxLiv }}%</div>
                    <div class="kpi-unit" id="kpiTauxUnit">{{ $livres }} / {{ $totalCmdMonth }} livrées</div>
                    <div class="kpi-delta {{ $tauxLiv >= 90 ? 'up':'down' }}" id="kpiTauxDelta">{{ $tauxLiv >= 90 ? '✓ Excellent':'⚠ À améliorer' }}</div>
                </div>

            </div>
