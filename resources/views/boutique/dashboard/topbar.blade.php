        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu" aria-label="Menu">{!! $I['menu'] !!}</button>
            <div class="tb-info">
                <h1 class="tb-greeting sv-title"><span class="sv-title-ico" aria-hidden="true">{!! $I['grid'] !!}</span> Tableau de bord</h1>
                <div class="tb-greeting-sub">
                    {{ $shop->name }}
                    <span id="dashLastUpdate" style="margin-left:6px;font-size:inherit;color:var(--muted);font-weight:500"></span>
                </div>
            </div>

            

            <div class="tb-actions">

                {{-- Mode sombre --}}
                <button class="tb-icon-btn" id="btnDarkMode" data-dark-toggle
                        data-moon-icon="{{ $I['moon'] }}" data-sun-icon="{{ $I['sun'] }}"
                        title="Mode sombre">{!! $I['moon'] !!}</button>

                {{-- Cloche notifications temps réel --}}
                <div class="bq-notif-backdrop" id="bqNotifBackdrop"></div>
                <div class="notif-bell-wrap" id="notifBellWrap" style="position:relative;display:inline-flex">
                    <button class="btn btn-sm" id="notifBellBtn" onclick="toggleNotifDropdown()" title="Notifications" style="position:relative;padding:6px 10px;display:inline-flex;align-items:center;gap:4px">
                        {!! $I['bell'] !!}
                        <span id="notifBellCount" style="display:none;position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;min-width:16px;height:16px;border-radius:20px;padding:0 3px;align-items:center;justify-content:center;border:2px solid #fff"></span>
                    </button>
                    <div id="notifDropdown" class="bq-notif-panel">
                        <div class="bq-np-hd">
                            <div class="bq-np-title">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                Notifications
                            </div>
                            <span class="bq-np-cnt" id="notifDropdownTotal" style="display:none">0</span>
                        </div>
                        <div id="notifList" class="bq-np-list"></div>
                        <div class="bq-np-foot">
                            <a href="{{ route('boutique.orders.index') }}" class="btn btn-sm">{!! $I['box_sm'] !!} Commandes</a>
                            <a href="{{ route('boutique.messages.hub') }}" class="btn btn-sm">{!! $I['msg_sm'] !!} Messages</a>
                            <a href="{{ route('support.index') }}" class="btn btn-sm" style="flex:1;justify-content:center;font-size:11px;gap:4px">{!! $I['hdp_sm'] !!} Support</a>
                        </div>
                    </div>
                </div>

                {{-- Bouton Messages (depuis partial) --}}
                @include('boutique._partials.messages_btn_and_drawer')

                {{-- Export grand écran --}}
                <div class="topbar-export-group">
                    <a href="{{ route('boutique.export.orders.excel', ['shop_id' => $shop->id]) }}" class="btn btn-sm" style="gap:4px" data-noprogress>{!! $I['download'] !!} Excel</a>
                    <a href="{{ route('boutique.export.orders.pdf',   ['shop_id' => $shop->id]) }}" class="btn btn-sm" style="gap:4px" data-noprogress>{!! $I['download'] !!} PDF</a>
                </div>

                {{-- Export petit écran : dropdown --}}
                <div class="topbar-export-dropdown">
                    <button class="export-dropdown-btn" onclick="toggleExportMenu(this)" type="button" style="gap:4px">{!! $I['download'] !!} ▾</button>
                    <div class="export-menu" id="exportMenu">
                        <a href="{{ route('boutique.export.orders.excel', ['shop_id' => $shop->id]) }}" style="gap:8px" data-noprogress>{!! $I['table_sm'] !!} Excel</a>
                        <a href="{{ route('boutique.export.orders.pdf',   ['shop_id' => $shop->id]) }}" style="gap:8px" data-noprogress>{!! $I['file_sm'] !!} PDF</a>
                    </div>
                </div>

                {{-- Nouvelle commande --}}
                <a href="{{ route('boutique.orders.index') }}" class="btn btn-primary btn-sm">
                    + <span class="btn-commande-label">Commande</span>
                </a>
            </div>
        </div>
