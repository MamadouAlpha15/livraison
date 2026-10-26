<div class="msg-overlay" id="msgOverlay" onclick="closeMsgDrawer()"></div>
<div class="msg-drawer" id="msgDrawer">
    <div class="msg-drawer-hd">
        <span class="msg-drawer-title">{!! $si('chat',18) !!} Mes Messages</span>
        @if($myUnread > 0)
        <span class="msg-drawer-badge">{{ $myUnread }} non lu{{ $myUnread > 1 ? 's' : '' }}</span>
        @endif
        <button class="msg-drawer-close" onclick="closeMsgDrawer()">{!! $si('x',14) !!}</button>
    </div>
    <div class="msg-conv-list" id="msgConvList">
        @forelse($myMessages as $convKey => $msgs)
        @php
            $firstMsg = $msgs->first();
            $lastMsg  = $msgs->first(); /* déjà orderByDesc donc premier = plus récent */
            $product  = $firstMsg->product;
            $shop     = $product?->shop;
            $vendeur  = ($firstMsg->sender_id === $user->id) ? $firstMsg->receiver : $firstMsg->sender;
            $unreadCnt = $msgs->filter(fn($m) => is_null($m->read_at) && $m->receiver_id === $user->id)->count();
            $vName  = $shop?->name ?? ($vendeur?->name ?? 'Vendeur');
            $vParts = explode(' ', $vName);
            $vInit  = strtoupper(substr($vParts[0],0,1)) . strtoupper(substr($vParts[1] ?? 'X',0,1));
            $convData = json_encode([
                'key'      => $convKey,
                'shopName' => $vName,
                'shopImg'  => $shop?->image ? asset('storage/'.$shop->image) : null,
                'shopInit' => $vInit,
                'prodName' => $product?->name ?? '',
                'prodImg'  => $product?->image ? asset('storage/'.$product->image) : null,
                'prodPrice'=> $product ? number_format($product->price,0,',',' ').' GNF' : '',
                'prodUrl'  => $product ? route('client.products.show', $product) : '#',
                'productId'=> $product?->id,
                'msgs'     => $msgs->map(fn($m) => [
                    'id'   => $m->id,
                    'body' => $m->body,
                    'mine' => $m->sender_id === $user->id,
                    'av'   => $m->sender_id === $user->id ? $initials : $vInit,
                    'time' => $m->created_at->format('d/m H:i'),
                    'read' => !is_null($m->read_at),
                ])->values(),
            ]);
        @endphp
        <div class="msg-conv-item {{ $unreadCnt > 0 ? 'has-unread' : '' }}"
             onclick="openMsgModal({{ $convData }})" data-conv-key="{{ $convKey }}">
            <div class="msg-conv-av">
                @if($shop?->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }}"
                         alt="{{ $vName }}" loading="lazy" decoding="async" width="42" height="42">
                @else
                    {{ $vInit }}
                @endif
                @if($unreadCnt > 0)<span class="msg-conv-av-dot"></span>@endif
            </div>
            <div class="msg-conv-info">
                <div class="msg-conv-name">{{ $vName }}</div>
                @if($product)<div class="msg-conv-prod">{!! $si('tag',11) !!} {{ Str::limit($product->name, 28) }}</div>@endif
                <div class="msg-conv-preview">{{ Str::limit($lastMsg->body, 42) }}</div>
            </div>
            <div class="msg-conv-meta">
                <span class="msg-conv-time">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
                @if($unreadCnt > 0)<span class="msg-conv-unread">{{ $unreadCnt }}</span>@endif
            </div>
        </div>
        @empty
        <div class="msg-conv-empty">
            <span class="msg-conv-empty-ico">{!! $si('chat',40) !!}</span>
            <div class="msg-conv-empty-txt">Aucune conversation pour l'instant.<br>Posez une question depuis une boutique !</div>
        </div>
        @endforelse
    </div>
</div>

{{-- ══ DRAWER FAVORIS ══ --}}
<div class="fav-overlay" id="favOverlay" onclick="closeFavDrawer()"></div>
<div class="fav-drawer" id="favDrawer">
    <div class="fav-drawer-hd">
        <div class="fav-drawer-ico">{!! $si('heart',20) !!}</div>
        <div>
            <div class="fav-drawer-title">Mes Favoris</div>
            <div class="fav-drawer-sub" id="favDrawerSub">{{ $favCount }} boutique{{ $favCount !== 1 ? 's' : '' }} sauvegardée{{ $favCount !== 1 ? 's' : '' }}</div>
        </div>
        <button class="fav-drawer-close" onclick="closeFavDrawer()">{!! $si('x',14) !!}</button>
    </div>
    <div class="fav-list" id="favList">
        <div class="fav-empty" id="favEmptyState" style="{{ $favCount > 0 ? 'display:none' : '' }}">
            <div class="fav-empty-ico">{!! $si('heart',32) !!}</div>
            <div class="fav-empty-title">Aucun favori</div>
            <div class="fav-empty-sub">Cliquez sur le cœur d'une boutique<br>pour la sauvegarder ici.</div>
        </div>
        <div id="favShopsContainer">
            {{-- Rempli dynamiquement via JS --}}
        </div>
    </div>
    <div class="fav-drawer-footer" id="favDrawerFooter" style="{{ $favCount === 0 ? 'display:none' : '' }}">
        <a href="{{ route('client.dashboard') }}#catalogue" onclick="closeFavDrawer()" class="fav-visit-all-btn">
            {!! $si('bag',16) !!} Découvrir des produits
        </a>
    </div>
</div>

{{-- ══ MODAL DISCUSSION ══ --}}
<div class="msg-modal-overlay" id="msgModalOverlay">
    <div class="msg-modal" id="msgModal">
        <div class="msg-modal-hd">
            <button class="msg-modal-back" onclick="closeMsgModal()" title="Retour">{!! $si('arrow-l',15) !!}</button>
            <div class="msg-modal-av" id="mmAv">??</div>
            <div class="msg-modal-info">
                <div class="msg-modal-name" id="mmName">Vendeur</div>
                <div class="msg-modal-prod" id="mmProd"></div>
            </div>
            <button class="msg-modal-close" onclick="closeMsgModal(); closeMsgDrawer()">{!! $si('x',14) !!}</button>
        </div>
        <div class="msg-prod-bar" id="mmProdBar" style="display:none">
            <div class="msg-prod-ph" id="mmProdImgPh">{!! $si('tag',18) !!}</div>
            <img class="msg-prod-img" id="mmProdImg" src="" alt="" style="display:none">
            <div>
                <div class="msg-prod-name" id="mmProdName"></div>
                <div class="msg-prod-price" id="mmProdPrice"></div>
            </div>
            <a class="msg-prod-link" id="mmProdLink" href="#" target="_blank">Voir →</a>
        </div>
        <div class="msg-thread" id="mmThread">
            <div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px">
                Chargement…
            </div>
        </div>
        <div class="msg-reply-zone">
            <form id="mmForm" style="display:flex;gap:10px;align-items:flex-end;width:100%" onsubmit="sendMsg(event)">
                @csrf
                <input type="hidden" id="mmProductId" name="product_id" value="">
                <textarea name="body" id="mmInput" class="msg-reply-input"
                    placeholder="Écrire au vendeur…" rows="1" required
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg(event)}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                <button type="submit" class="msg-send-btn">{!! $si('send',16) !!}</button>
            </form>
        </div>
    </div>
</div>
