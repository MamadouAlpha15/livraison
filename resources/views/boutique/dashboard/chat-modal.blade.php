<div class="bq-chat-overlay" id="bqChatModal" style="display:none">
    <div class="bq-chat-panel">
        <div class="bq-chat-hd">
            <div class="bq-chat-hd-info">
                <div class="bq-chat-hd-av" id="bqChatAv">{!! $I['bldg_chat'] !!}</div>
                <div>
                    <div class="bq-chat-hd-name" id="bqChatName">Entreprise</div>
                    <div class="bq-chat-hd-sub" id="bqChatSub">Entreprise de livraison partenaire</div>
                </div>
            </div>
            <button class="bq-chat-close" onclick="bqCloseChatModal()">{!! $I['close_chat'] !!}</button>
        </div>
        {{-- Toggle livraison (mobile only) --}}
        <button class="bq-confier-toggle" id="bqConfierToggle" onclick="bqToggleConfierZone()" type="button" style="display:none;">
            <span class="bq-ct-left">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span class="bq-ct-label">Livraison</span>
                <span class="bq-ct-badge" id="bqConfierBadge">Commandes dispo</span>
            </span>
            <span class="bq-ct-arrow">▾</span>
        </button>

        {{-- Zone confier la livraison (au-dessus des messages pour ne pas masquer le dernier) --}}
        <div class="bq-confier-zone" id="bqConfierZone" style="display:none;">
            <div class="bq-confier-hint" style="display:flex;align-items:flex-start;gap:6px">
                {!! $I['box_conf'] !!} <strong>Confier une livraison</strong>
                <span style="display:block;font-size:10.5px;color:#047857;font-weight:400;margin-top:2px;">
                    Cochez une ou plusieurs commandes → choisissez une zone → cliquez Confier
                </span>
            </div>
            <div id="bqOrdersList" style="display:flex;flex-direction:column;gap:5px;max-height:150px;overflow-y:auto;margin-bottom:8px;scrollbar-width:thin;scrollbar-color:#86efac #f0fdf4;"></div>
            <div id="bqZonePickerWrap" style="display:none;margin-bottom:8px;">
                <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:5px;margin-bottom:8px;">{!! $I['pin_zone'] !!} Zone de livraison</label>
                {{-- Champ de recherche --}}
                <div id="bqZoneSearchBox">
                    <div style="position:relative;display:flex;align-items:center;margin-bottom:6px;">
                        <span style="position:absolute;left:10px;pointer-events:none;font-size:14px;color:#9ca3af;">🔍</span>
                        <input type="text" id="bqZoneSearch" placeholder="Tapez le nom d'une zone…" autocomplete="off"
                               oninput="bqFilterZones(this.value)"
                               style="width:100%;padding:10px 34px 10px 34px;border:1.5px solid #bbf7d0;border-radius:10px;font-size:16px;font-family:inherit;background:#fff;color:#0f172a;outline:none;box-sizing:border-box;transition:border-color .15s;"
                               onfocus="this.style.borderColor='#059669'" onblur="this.style.borderColor='#bbf7d0'">
                        <button id="bqZoneSearchClear" onclick="bqZoneClearSearch()" style="display:none;position:absolute;right:8px;background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;padding:4px;line-height:1;touch-action:manipulation;">✕</button>
                    </div>
                    <div id="bqZoneResults" style="display:flex;flex-direction:column;gap:4px;max-height:160px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#86efac #f0fdf4;"></div>
                </div>
                {{-- Zone sélectionnée (chip) --}}
                <div id="bqZoneSelectedChip" style="display:none;"></div>
            </div>
            <button class="bq-btn-confier" id="bqBtnConfier" onclick="bqConfierLivraison()" style="gap:6px">
                {!! $I['box_conf'] !!} Confier la livraison à cette entreprise
            </button>
        </div>

        <div class="bq-chat-msgs" id="bqChatMsgList">
            <div class="bq-chat-empty" id="bqChatEmpty">Chargement…</div>
        </div>

        <div class="bq-chat-input-zone">
            <textarea class="bq-chat-textarea" id="bqChatInput" placeholder="Votre message…" rows="1"
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();bqSendMsg();}"></textarea>
            <button class="bq-chat-send-btn" id="bqChatSendBtn" onclick="bqSendMsg()" title="Envoyer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </div>
</div>
