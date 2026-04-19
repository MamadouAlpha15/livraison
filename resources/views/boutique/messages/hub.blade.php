{{-- resources/views/boutique/messages/hub.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Messages — {{ $shop->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --orange: #f90; --orange-dk: #e47911; --navy: #131921; --navy-2: #232f3e;
    --green: #25d366; --green-dk: #128c7e; --border: #e9edef;
    --text: #111b21; --muted: #667781; --surface: #fff; --bg: #f0f2f5;
    --font: 'Inter', sans-serif; --nav-h: 56px; --sidebar-w: 360px;
}
html, body { height: 100%; font-family: var(--font); background: var(--bg); color: var(--text); overflow: hidden; }

/* ═══════════ TOPBAR ═══════════ */
.topbar {
    height: var(--nav-h); background: var(--navy-2); display: flex; align-items: center;
    padding: 0 20px; gap: 14px; position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
}
.topbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.topbar-logo img { height: 30px; border-radius: 6px; }
.topbar-logo-name { color: var(--orange); font-size: 17px; font-weight: 800; }
.topbar-sep { width: 1px; height: 24px; background: rgba(255,255,255,.15); }
.topbar-title { color: rgba(255,255,255,.9); font-size: 15px; font-weight: 600; }
.topbar-back {
    margin-left: auto; color: rgba(255,255,255,.75); font-size: 13px; font-weight: 600;
    text-decoration: none; padding: 7px 14px; border: 1px solid rgba(255,255,255,.2);
    border-radius: 6px; transition: all .15s;
}
.topbar-back:hover { background: rgba(255,255,255,.1); color: #fff; }

/* ═══════════ LAYOUT ═══════════ */
.hub { display: flex; height: calc(100vh - var(--nav-h)); margin-top: var(--nav-h); }

/* ═══════════ SIDEBAR ═══════════ */
.hub-sidebar {
    width: var(--sidebar-w); flex-shrink: 0;
    display: flex; flex-direction: column;
    background: var(--surface); border-right: 1px solid var(--border);
}
.hub-sidebar-head {
    padding: 14px 16px 10px; background: #f0f2f5;
    border-bottom: 1px solid var(--border);
}
.hub-sidebar-title { font-size: 18px; font-weight: 700; color: var(--text); }
.hub-sidebar-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }
.hub-search-wrap { padding: 8px 10px; border-bottom: 1px solid var(--border); background: var(--surface); }
.hub-search {
    width: 100%; padding: 9px 14px; border-radius: 8px;
    border: none; background: #f0f2f5; font-size: 13.5px; outline: none; font-family: var(--font);
}
.hub-search::placeholder { color: var(--muted); }
.hub-conv-list { flex: 1; overflow-y: auto; }
.hub-conv-list::-webkit-scrollbar { width: 4px; }
.hub-conv-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

/* Conversation item */
.hub-conv-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 16px; cursor: pointer; border-bottom: 1px solid #f5f6f6;
    transition: background .12s; position: relative;
}
.hub-conv-item:hover { background: #f5f6f6; }
.hub-conv-item.active { background: #f0f2f5; }
.hub-conv-av {
    width: 50px; height: 50px; border-radius: 50%;
    background: linear-gradient(135deg, #075e54, #128c7e);
    color: #fff; font-size: 15px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
}
.hub-conv-body { flex: 1; min-width: 0; }
.hub-conv-name { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hub-conv-preview { font-size: 12.5px; color: var(--muted);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hub-conv-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }
.hub-conv-time { font-size: 11px; color: var(--muted); }
.hub-conv-badge {
    background: var(--green); color: #fff; font-size: 11px; font-weight: 700;
    border-radius: 50%; min-width: 20px; height: 20px;
    display: flex; align-items: center; justify-content: center; padding: 0 5px;
}
.hub-conv-empty { padding: 40px 20px; text-align: center; color: var(--muted); font-size: 13.5px; }
.hub-conv-empty-ico { font-size: 40px; display: block; margin-bottom: 10px; opacity: .4; }

/* ═══════════ MAIN AREA ═══════════ */
.hub-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* Welcome */
.hub-welcome {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 14px;
}
.hub-welcome-ico { font-size: 70px; opacity: .2; }
.hub-welcome-title { font-size: 22px; font-weight: 600; color: var(--text); opacity: .45; }
.hub-welcome-sub { font-size: 14px; color: var(--muted); }

/* Chat area */
.hub-chat { flex: 1; display: none; flex-direction: column; overflow: hidden; }

/* Chat header */
.hub-chat-head {
    background: #f0f2f5; border-bottom: 1px solid var(--border);
    padding: 10px 16px; display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.hub-chat-av {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #075e54, #128c7e);
    color: #fff; font-size: 13px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.hub-chat-info { flex: 1; min-width: 0; }
.hub-chat-name { font-size: 14.5px; font-weight: 700; color: var(--text); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hub-chat-sub { font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Bande produit */
.hub-prod-strip {
    background: #fff; border-bottom: 2px solid var(--green);
    padding: 10px 16px; display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.hub-prod-img {
    width: 54px; height: 54px; border-radius: 8px; object-fit: cover;
    border: 2px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,.12); flex-shrink: 0;
}
.hub-prod-img-ph {
    width: 54px; height: 54px; border-radius: 8px; background: #f0f2f5;
    border: 2px solid var(--border); display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.hub-prod-info { flex: 1; min-width: 0; }
.hub-prod-label { font-size: 10px; font-weight: 700; color: var(--green-dk); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
.hub-prod-name { font-size: 13.5px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hub-prod-price { font-size: 13px; color: #b12704; font-weight: 800; font-family: monospace; margin-top: 1px; }
.hub-prod-img { cursor: pointer; transition: opacity .15s; }
.hub-prod-img:hover { opacity: .85; }
.hub-prod-img-ph { cursor: pointer; }
.hub-prod-view-btn {
    font-size: 12px; color: var(--green-dk); text-decoration: none; font-weight: 700;
    white-space: nowrap; flex-shrink: 0; padding: 7px 13px; border-radius: 8px;
    border: 1.5px solid var(--green); background: #d1fae5; transition: all .15s;
}
.hub-prod-view-btn:hover { background: #a7f3d0; }

/* Barre d'actions rapides vendeur */
.hub-vendor-actions {
    background: #fffbeb; border-bottom: 1px solid #fde68a;
    padding: 8px 16px; display: flex; gap: 8px; align-items: center; flex-shrink: 0;
    flex-wrap: wrap;
}
.hub-action-btn {
    padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
    cursor: pointer; border: none; transition: all .15s; font-family: var(--font);
}
.hub-action-offer  { background: #d1fae5; color: #065f46; }
.hub-action-offer:hover  { background: #a7f3d0; }
.hub-action-refuse { background: #fee2e2; color: #991b1b; }
.hub-action-refuse:hover { background: #fca5a5; }

/* Thread */
.hub-thread {
    flex: 1; overflow-y: auto; padding: 16px 12px;
    display: flex; flex-direction: column; gap: 4px;
    background: #efeae2 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='1.5' fill='%23c8b89a' opacity='.18'/%3E%3C/svg%3E");
}
.hub-thread::-webkit-scrollbar { width: 5px; }
.hub-thread::-webkit-scrollbar-thumb { background: #c5c5c5; border-radius: 5px; }

/* Date séparateur */
.hub-date-sep { text-align: center; margin: 10px 0; }
.hub-date-sep span {
    background: #fff; color: var(--muted); font-size: 11.5px; font-weight: 600;
    padding: 4px 12px; border-radius: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.1);
}

/* Message rows */
.hub-msg-row { display: flex; max-width: 68%; margin-bottom: 2px; }
.hub-msg-row.mine { margin-left: auto; justify-content: flex-end; }

.hub-msg-bubble {
    padding: 9px 13px; border-radius: 8px; font-size: 13.5px;
    line-height: 1.55; word-break: break-word; max-width: 100%;
    box-shadow: 0 1px 2px rgba(0,0,0,.13);
}
.hub-msg-row.mine   .hub-msg-bubble { background: #dcf8c6; color: #111; border-bottom-right-radius: 2px; }
.hub-msg-row.theirs .hub-msg-bubble { background: #fff; color: #111; border-bottom-left-radius: 2px; }

.hub-msg-meta { display: flex; align-items: center; gap: 4px; margin-top: 3px; justify-content: flex-end; }
.hub-msg-time { font-size: 10.5px; color: #667781; }
.hub-msg-tick { font-size: 11px; color: #53bdeb; }

.hub-thread-loader, .hub-thread-empty { text-align: center; padding: 60px 20px; color: var(--muted); font-size: 13.5px; }

/* Input zone */
.hub-input-zone {
    background: #f0f2f5; border-top: 1px solid var(--border);
    padding: 10px 14px; display: flex; gap: 10px; align-items: flex-end; flex-shrink: 0;
}
.hub-textarea {
    flex: 1; padding: 10px 16px; border-radius: 24px; border: none;
    background: #fff; font-size: 13.5px; font-family: var(--font);
    outline: none; resize: none; min-height: 42px; max-height: 120px;
    line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,.1);
}
.hub-send-btn {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--green); color: #fff; border: none;
    cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;
    transition: background .15s, transform .1s; box-shadow: 0 2px 6px rgba(37,211,102,.35);
}
.hub-send-btn:hover { background: var(--green-dk); transform: scale(1.06); }
.hub-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Toast */
.hub-toast {
    position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(80px);
    background: #111; color: #fff; padding: 11px 22px; border-radius: 24px;
    font-size: 13px; font-weight: 600; z-index: 9999;
    transition: transform .3s cubic-bezier(.23,1,.32,1); pointer-events: none;
}
.hub-toast.show { transform: translateX(-50%) translateY(0); }
.hub-toast.ok   { background: #075e54; }
.hub-toast.err  { background: #b91c1c; }

/* ── Nego cards ── */
.nego-card {
    max-width: 310px; border-radius: 12px; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.13); margin-bottom: 2px; width: 100%;
}
.nego-card-head { padding: 9px 14px 7px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; }
.nego-card-body { padding: 10px 14px; }
.nego-card-price { font-size: 21px; font-weight: 900; font-family: monospace; margin: 2px 0 5px; }
.nego-card-note  { font-size: 12px; line-height: 1.5; opacity: .85; }
.nego-card-actions { padding: 8px 14px 12px; display: flex; gap: 8px; flex-wrap: wrap; }
.nego-card-btn { padding: 7px 16px; border-radius: 20px; font-size: 12.5px; font-weight: 700; cursor: pointer; border: none; font-family: var(--font); transition: all .15s; }
/* proposal (client envoyé → jaune) */
.nego-proposal { background: #fffbeb; border: 1.5px solid #f59e0b; }
.nego-proposal .nego-card-head { background: #fef3c7; color: #92400e; }
.nego-proposal .nego-card-price { color: #b45309; }
.nego-btn-accept { background: #d1fae5; color: #065f46; }
.nego-btn-accept:hover { background: #a7f3d0; }
.nego-btn-refuse { background: #fee2e2; color: #991b1b; }
.nego-btn-refuse:hover { background: #fca5a5; }
/* offer (vendeur envoyé → vert) */
.nego-offer { background: #f0fdf4; border: 1.5px solid var(--green); }
.nego-offer .nego-card-head { background: #dcfce7; color: #065f46; }
.nego-offer .nego-card-price { color: #16a34a; }
/* refused (refusé → rouge clair) */
.nego-refused { background: #fff1f2; border: 1.5px solid #fca5a5; }
.nego-refused .nego-card-head { background: #fee2e2; color: #991b1b; }
.nego-refused .nego-card-price { color: #b91c1c; text-decoration: line-through; opacity: .7; }
/* order (confirmé → bleu) */
.nego-order { background: #eff6ff; border: 1.5px solid #3b82f6; }
.nego-order .nego-card-head { background: #dbeafe; color: #1e40af; }
.nego-order .nego-card-price { color: #1d4ed8; }
/* status badge */
.nego-status { display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 20px; margin-bottom: 6px; }
.nego-status.pending  { background: #fef3c7; color: #92400e; }
.nego-status.accepted { background: #d1fae5; color: #065f46; }
.nego-status.refused  { background: #fee2e2; color: #991b1b; }
.nego-status.confirmed{ background: #dbeafe; color: #1e40af; }

/* ── Offer panel ── */
.hub-offer-panel {
    background: #f0fdf4; border-top: 2px solid var(--green);
    padding: 12px 16px; flex-shrink: 0; display: none; flex-direction: column; gap: 8px;
}
.hub-offer-panel.open { display: flex; }
.hub-offer-panel-title { font-size: 12px; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: .4px; }
.hub-offer-hint { font-size: 11.5px; color: var(--muted); }
.hub-offer-panel-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.hub-offer-input { flex: 1; padding: 9px 14px; border-radius: 8px; border: 1.5px solid var(--green); font-size: 14px; font-family: var(--font); outline: none; min-width: 120px; }
.hub-offer-devise { font-size: 13px; font-weight: 700; color: var(--muted); flex-shrink: 0; }
.hub-offer-submit { padding: 9px 20px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: var(--font); transition: background .15s; }
.hub-offer-submit:hover { background: var(--green-dk); }
.hub-offer-cancel { padding: 9px 16px; background: transparent; color: var(--muted); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; cursor: pointer; font-family: var(--font); }
.hub-offer-cancel:hover { background: #f5f6f6; }

/* ── Back button (mobile) ── */
.hub-back-btn {
    display: none; background: none; border: none;
    font-size: 22px; color: var(--muted); cursor: pointer;
    padding: 4px 6px; border-radius: 6px; flex-shrink: 0;
    line-height: 1;
}
.hub-back-btn:hover { background: #e9edef; }

/* ── Tablette large (≤1100px) ── */
@media (max-width: 1100px) {
    :root { --sidebar-w: 300px; }
}

/* ── Tablette (≤850px) ── */
@media (max-width: 850px) {
    :root { --sidebar-w: 250px; }
    .topbar-logo-name { font-size: 14px; }
    .topbar-title { font-size: 13px; }
    .hub-prod-strip { padding: 8px 12px; gap: 8px; }
    .hub-prod-img, .hub-prod-img-ph { width: 46px; height: 46px; }
    .hub-prod-label { display: none; }
    #hubOfferTriggerBtn { display: none; }
    .hub-msg-row { max-width: 80%; }
    .nego-card { max-width: 90%; }
}

/* ── Mobile (≤640px) — sidebar cachée, toggle WhatsApp ── */
@media (max-width: 640px) {
    :root { --sidebar-w: 100%; --nav-h: 50px; }
    .hub-main { display: none; }
    .hub.conv-open .hub-sidebar { display: none; }
    .hub.conv-open .hub-main { display: flex; }
    .hub-back-btn { display: flex !important; }

    /* Topbar compacte */
    .topbar { padding: 0 12px; gap: 8px; }
    .topbar-logo img { height: 24px; }
    .topbar-logo-name { display: none; }
    .topbar-sep { display: none; }
    .topbar-title { font-size: 13px; }
    .topbar-back { font-size: 11px; padding: 5px 10px; }

    /* En-tête chat */
    .hub-chat-head { padding: 8px 10px; gap: 8px; }
    .hub-chat-av { width: 36px; height: 36px; font-size: 11px; }
    .hub-chat-name { font-size: 13px; }
    .hub-chat-sub { font-size: 11px; }

    /* Strip produit */
    .hub-prod-strip { padding: 7px 10px; gap: 8px; flex-wrap: nowrap; }
    .hub-prod-img, .hub-prod-img-ph { width: 42px; height: 42px; font-size: 18px; }
    .hub-prod-name { font-size: 12px; }
    .hub-prod-price { font-size: 11.5px; }
    .hub-prod-view-btn { padding: 5px 9px; font-size: 10.5px; }
    #hubOfferTriggerBtn { display: none; }

    /* Offer panel */
    .hub-offer-panel { padding: 10px 12px; }
    .hub-offer-input { font-size: 13px; padding: 8px 12px; min-width: 90px; }
    .hub-offer-submit { padding: 8px 14px; font-size: 12px; }
    .hub-offer-cancel { padding: 8px 10px; font-size: 11px; }

    /* Bulles */
    .hub-msg-row { max-width: 86%; }
    .hub-msg-bubble { font-size: 13px; }
    .nego-card { max-width: 92%; }
    .nego-card-price { font-size: 18px; }
    .nego-card-btn { padding: 6px 12px; font-size: 12px; }

    /* Thread & saisie */
    .hub-thread { padding: 12px 8px; }
    .hub-input-zone { padding: 7px 8px; gap: 7px; }
    .hub-textarea { font-size: 13px; padding: 8px 13px; }
    .hub-send-btn { width: 40px; height: 40px; font-size: 16px; }
}

/* ── Très petit mobile (≤400px) ── */
@media (max-width: 400px) {
    .topbar-back { display: none; }
    .hub-chat-head { padding: 7px 8px; }
    .hub-prod-strip { padding: 6px 8px; gap: 6px; }
    .hub-prod-img, .hub-prod-img-ph { width: 38px; height: 38px; }
    .hub-prod-name { font-size: 11px; }
    .hub-prod-view-btn { display: none; }
    .hub-msg-row { max-width: 92%; }
    .nego-card { max-width: 95%; }
    .hub-input-zone { padding: 6px; gap: 5px; }
    .hub-send-btn { width: 38px; height: 38px; font-size: 15px; }
}

/* ── Modal produit ── */
.prod-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 9000;
    align-items: center; justify-content: center;
    animation: fadeIn .2s ease;
}
.prod-modal-overlay.open { display: flex; }
@keyframes fadeIn { from{opacity:0}to{opacity:1} }

.prod-modal {
    background: #fff; border-radius: 16px; width: 420px; max-width: 92vw;
    max-height: 85vh; overflow-y: auto; position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    animation: slideUp .25s cubic-bezier(.23,1,.32,1);
}
@keyframes slideUp { from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1} }

.prod-modal-close {
    position: absolute; top: 12px; right: 12px; z-index: 1;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(0,0,0,.07); border: none; font-size: 15px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .15s; color: #555;
}
.prod-modal-close:hover { background: rgba(0,0,0,.15); }

.prod-modal-img-wrap { width: 100%; aspect-ratio: 4/3; overflow: hidden; border-radius: 16px 16px 0 0; background: #f0f2f5; display: flex; align-items: center; justify-content: center; }
.prod-modal-img { width: 100%; height: 100%; object-fit: cover; }
.prod-modal-img-ph { font-size: 60px; opacity: .35; }

.prod-modal-body { padding: 18px 20px 24px; }
.prod-modal-name { font-size: 18px; font-weight: 800; color: #111; margin-bottom: 8px; line-height: 1.3; }
.prod-modal-price { font-size: 22px; font-weight: 900; color: #b12704; font-family: monospace; margin-bottom: 6px; }
.prod-modal-stock { font-size: 12px; font-weight: 700; color: #065f46; background: #d1fae5; display: inline-flex; padding: 3px 10px; border-radius: 20px; margin-bottom: 12px; }
.prod-modal-stock.low { color: #92400e; background: #fef3c7; }
.prod-modal-stock.out { color: #991b1b; background: #fee2e2; }
.prod-modal-desc { font-size: 13.5px; color: #444; line-height: 1.65; white-space: pre-wrap; }

/* ── Galerie modal ── */
.prod-modal-img-wrap { position: relative; }
.pm-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(0,0,0,.45); color: #fff; border: none; border-radius: 50%;
    width: 38px; height: 38px; font-size: 22px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; z-index: 2; line-height: 1;
}
.pm-arrow:hover { background: rgba(0,0,0,.7); }
.pm-arrow-l { left: 10px; }
.pm-arrow-r { right: 10px; }
.pm-counter {
    position: absolute; bottom: 8px; right: 12px;
    background: rgba(0,0,0,.5); color: #fff; font-size: 11px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
}
.pm-thumbs {
    display: flex; gap: 8px; padding: 10px 16px; flex-wrap: nowrap;
    overflow-x: auto; background: #f9fafb; border-bottom: 1px solid var(--border);
}
.pm-thumbs:empty { display: none; }
.pm-thumbs::-webkit-scrollbar { height: 4px; }
.pm-thumbs::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
.pm-thumb {
    width: 60px; height: 60px; flex-shrink: 0; border-radius: 8px;
    object-fit: cover; cursor: pointer; border: 2px solid transparent;
    transition: border-color .15s, transform .15s;
}
.pm-thumb:hover { border-color: var(--green); transform: scale(1.05); }
.pm-thumb.active { border-color: var(--green); box-shadow: 0 0 0 2px #d1fae5; }
</style>
</head>
<body>

{{-- Topbar --}}
<header class="topbar">
    <a href="{{ route('boutique.dashboard') }}" class="topbar-logo">
        <img src="{{ asset('images/Shopio.jpeg') }}" alt="Shopio">
        <span class="topbar-logo-name">Shopio</span>
    </a>
    <div class="topbar-sep"></div>
    <span class="topbar-title">💬 Messages clients</span>
    <a href="{{ route('boutique.dashboard') }}" class="topbar-back">← Tableau de bord</a>
</header>

<div class="hub" id="hub">

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    <aside class="hub-sidebar">
        <div class="hub-sidebar-head">
            <div class="hub-sidebar-title">Conversations</div>
            <div class="hub-sidebar-sub">{{ $shop->name }}</div>
        </div>
        <div class="hub-search-wrap">
            <input type="search" class="hub-search" placeholder="Rechercher un client ou produit…"
                   oninput="filterConvs(this.value)">
        </div>
        <div class="hub-conv-list" id="convList">
            @forelse($conversations as $conv)
            @php
                $client   = $conv->client;
                $product  = $conv->product;
                $cName    = $client?->name ?? 'Client';
                $cParts   = explode(' ', $cName);
                $cInit    = strtoupper(substr($cParts[0],0,1)) . strtoupper(substr($cParts[1] ?? 'X',0,1));
                $preview  = Str::limit($conv->lastMsg->body ?? '…', 40);
                $timeAgo  = $conv->lastMsg->created_at->diffForHumans(null, true);
                $prodImg    = $product?->image ? asset('storage/'.$product->image) : '';
                $prodName   = $product?->name ?? '';
                $devise     = $shop->currency ?? 'GNF';
                $prodPrice  = $product ? number_format($product->price,0,',',' ').' '.$devise : '';
                $prodDesc   = $product?->description ?? '';
                $prodStock  = $product?->stock !== null ? $product->stock : null;
                $galleryArr = $product ? ($product->gallery_array ?? []) : [];
                $galleryUrls= array_map(fn($g) => asset('storage/'.$g), $galleryArr);
                $galleryJson= htmlspecialchars(json_encode(array_values($galleryUrls)), ENT_QUOTES, 'UTF-8');
            @endphp
            <div class="hub-conv-item"
                 data-client-id="{{ $conv->clientId }}"
                 data-client-init="{{ $cInit }}"
                 data-client-name="{{ $cName }}"
                 data-product-id="{{ $conv->productId }}"
                 data-prod-name="{{ $prodName }}"
                 data-prod-price="{{ $prodPrice }}"
                 data-prod-price-raw="{{ $product?->price ?? 0 }}"
                 data-devise="{{ $devise }}"
                 data-prod-img="{{ $prodImg }}"
                 data-prod-desc="{{ $prodDesc }}"
                 data-prod-gallery="{{ $galleryJson }}"
                 data-prod-stock="{{ $prodStock ?? '' }}"
                 data-search="{{ strtolower($cName . ' ' . $prodName) }}"
                 onclick="selectConv({{ $conv->clientId }}, {{ $conv->productId ?? 'null' }}, this)">
                <div class="hub-conv-av">{{ $cInit }}</div>
                <div class="hub-conv-body">
                    <div class="hub-conv-name">{{ $cName }}</div>
                    <div class="hub-conv-preview">
                        @if($product)🏷️ {{ Str::limit($product->name, 22) }} · @endif{{ $preview }}
                    </div>
                </div>
                <div class="hub-conv-meta">
                    <span class="hub-conv-time">{{ $timeAgo }}</span>
                    @if($conv->unread > 0)
                    <span class="hub-conv-badge">{{ $conv->unread }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="hub-conv-empty">
                <span class="hub-conv-empty-ico">💬</span>
                Aucun message client pour le moment.
            </div>
            @endforelse
        </div>
    </aside>

    {{-- ═══════════ MAIN ═══════════ --}}
    <main class="hub-main" id="hubMain">

        <div class="hub-welcome" id="hubWelcome">
            <div class="hub-welcome-ico">💬</div>
            <div class="hub-welcome-title">Messages clients</div>
            <div class="hub-welcome-sub">Sélectionnez une conversation</div>
        </div>

        <div class="hub-chat" id="hubChat">

            {{-- Header --}}
            <div class="hub-chat-head">
                <button class="hub-back-btn" id="hubBackBtn" onclick="backToList()">←</button>
                <div class="hub-chat-av" id="hubChatAv">?</div>
                <div class="hub-chat-info">
                    <div class="hub-chat-name" id="hubChatName">—</div>
                    <div class="hub-chat-sub" id="hubChatSub">—</div>
                </div>
            </div>

            {{-- Bande produit --}}
            <div class="hub-prod-strip" id="hubProdStrip" style="display:none">
                <a href="#" id="hubProdImgLink" target="_blank" style="flex-shrink:0;display:block">
                    <div class="hub-prod-img-ph" id="hubProdImgPh">🏷️</div>
                    <img class="hub-prod-img" id="hubProdImg" src="" alt="" style="display:none">
                </a>
                <div class="hub-prod-info">
                    <div class="hub-prod-label">Produit en discussion</div>
                    <div class="hub-prod-name" id="hubProdName">—</div>
                    <div class="hub-prod-price" id="hubProdPrice"></div>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;align-items:flex-end">
                    <a class="hub-prod-view-btn" id="hubProdViewBtn" href="#" target="_blank">Voir →</a>
                    <button class="hub-prod-view-btn" id="hubOfferTriggerBtn"
                            onclick="showOfferPanel(null, null)"
                            style="background:#d1fae5;border-color:var(--green-dk);color:#065f46;cursor:pointer;font-family:var(--font)">
                        💰 Faire une offre
                    </button>
                </div>
            </div>

            {{-- Offer panel --}}
            <div class="hub-offer-panel" id="hubOfferPanel">
                <div class="hub-offer-panel-title">💰 Envoyer une offre de prix au client</div>
                <div class="hub-offer-hint" id="hubOfferHint"></div>
                <div class="hub-offer-panel-row">
                    <input type="number" class="hub-offer-input" id="hubOfferInput"
                           placeholder="Prix à proposer" min="1" step="1">
                    <span class="hub-offer-devise" id="hubOfferDevise">GNF</span>
                    <button class="hub-offer-submit" onclick="submitOffer()">Envoyer ✓</button>
                    <button class="hub-offer-cancel" onclick="closeOfferPanel()">Annuler</button>
                </div>
            </div>

            {{-- Thread --}}
            <div class="hub-thread" id="hubThread">
                <div class="hub-thread-loader">⏳ Chargement…</div>
            </div>

            {{-- Input --}}
            <div class="hub-input-zone">
                <textarea id="hubInput" class="hub-textarea" placeholder="Écrire un message au client…" rows="1"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendHubMsg()}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
                <button class="hub-send-btn" onclick="sendHubMsg()" id="hubSendBtn" title="Envoyer">➤</button>
            </div>
        </div>
    </main>
</div>

<div class="hub-toast" id="hubToast"></div>

{{-- ═══ MODAL DÉTAIL PRODUIT ═══ --}}
<div class="prod-modal-overlay" id="prodModalOverlay" onclick="closeProdModal()">
    <div class="prod-modal" onclick="event.stopPropagation()">
        <button class="prod-modal-close" onclick="closeProdModal()">✕</button>

        {{-- Image principale --}}
        <div class="prod-modal-img-wrap" id="pmImgWrap">
            <div class="prod-modal-img-ph" id="pmImgPh">🏷️</div>
            <img class="prod-modal-img" id="pmImg" src="" alt="" style="display:none">
            {{-- Flèches navigation --}}
            <button class="pm-arrow pm-arrow-l" id="pmArrowL" onclick="pmNav(-1)" style="display:none">‹</button>
            <button class="pm-arrow pm-arrow-r" id="pmArrowR" onclick="pmNav(1)"  style="display:none">›</button>
            <div class="pm-counter" id="pmCounter" style="display:none"></div>
        </div>

        {{-- Miniatures galerie --}}
        <div class="pm-thumbs" id="pmThumbs"></div>

        <div class="prod-modal-body">
            <div class="prod-modal-name" id="pmName">—</div>
            <div class="prod-modal-price" id="pmPrice"></div>
            <div class="prod-modal-stock" id="pmStock"></div>
            <div class="prod-modal-desc" id="pmDesc" style="white-space:pre-wrap"></div>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let _clientId  = null;
let _productId = null;
let _convEl    = null;
let _lastMsgId = 0;
let _pollTimer = null;
let _offerProposalMsgId = null; /* ID du message de proposition lié à l'offre en cours */
let _currentDevise = 'GNF';

/* ── Toast ── */
function showToast(msg, type = 'ok') {
    const el = document.getElementById('hubToast');
    el.textContent = msg;
    el.className = `hub-toast ${type} show`;
    setTimeout(() => el.classList.remove('show'), 3500);
}

/* ── Filtrer ── */
function backToList() {
    document.getElementById('hub').classList.remove('conv-open');
    stopPoll();
}

function filterConvs(q) {
    const lq = q.toLowerCase();
    document.querySelectorAll('.hub-conv-item').forEach(el => {
        el.style.display = el.dataset.search.includes(lq) ? '' : 'none';
    });
}

/* ── Sélectionner une conversation ── */
async function selectConv(clientId, productId, el) {
    document.querySelectorAll('.hub-conv-item').forEach(i => i.classList.remove('active'));
    if (el) { el.classList.add('active'); const b = el.querySelector('.hub-conv-badge'); if(b) b.remove(); }

    _clientId  = clientId;
    _productId = productId;
    _convEl    = el;

    /* Header client depuis data-* */
    const d     = el?.dataset || {};
    const cInit = d.clientInit || '?';
    const cName = d.clientName || '—';

    document.getElementById('hubChatAv').textContent   = cInit;
    document.getElementById('hubChatName').textContent = cName;
    document.getElementById('hubChatSub').textContent  = d.prodName ? '🏷️ ' + d.prodName : 'Discussion générale';

    /* Bande produit */
    updateProdStrip(d);

    document.getElementById('hub').classList.add('conv-open');
    document.getElementById('hubWelcome').style.display = 'none';
    document.getElementById('hubChat').style.display    = 'flex';

    const thread = document.getElementById('hubThread');
    thread.innerHTML = '<div class="hub-thread-loader">⏳ Chargement…</div>';
    _lastMsgId = 0;

    stopPoll();
    await loadConv();
    markRead();
    startPoll();
}

/* ── Bande produit depuis data-* ── */
let _currentProdData = {};

function updateProdStrip(d) {
    const strip = document.getElementById('hubProdStrip');
    if (!d.prodName) { strip.style.display = 'none'; return; }

    _currentProdData = d;
    _currentDevise   = d.devise || 'GNF';
    document.getElementById('hubOfferDevise').textContent = _currentDevise;
    closeOfferPanel();

    strip.style.display = 'flex';
    document.getElementById('hubProdName').textContent  = d.prodName;
    document.getElementById('hubProdPrice').textContent = d.prodPrice || '';

    /* Bouton "Voir le produit" → ouvre le modal */
    const viewBtn = document.getElementById('hubProdViewBtn');
    viewBtn.removeAttribute('href');
    viewBtn.onclick = () => openProdModal();

    /* Image cliquable → ouvre le modal */
    const imgLink = document.getElementById('hubProdImgLink');
    imgLink.removeAttribute('href');
    imgLink.onclick = (e) => { e.preventDefault(); openProdModal(); };

    const img = document.getElementById('hubProdImg');
    const ph  = document.getElementById('hubProdImgPh');
    if (d.prodImg) {
        img.src = d.prodImg; img.alt = d.prodName;
        img.style.display = ''; ph.style.display = 'none';
    } else {
        img.style.display = 'none'; ph.style.display = 'flex';
    }
}

/* ── Modal détail produit (avec galerie) ── */
let _pmPhotos = [];
let _pmIndex  = 0;

function openProdModal() {
    const d = _currentProdData;
    if (!d.prodName) return;

    /* Construire la liste complète des photos */
    _pmPhotos = [];
    if (d.prodImg) _pmPhotos.push(d.prodImg);
    try {
        const gallery = JSON.parse(d.prodGallery || '[]');
        gallery.forEach(url => { if (url && !_pmPhotos.includes(url)) _pmPhotos.push(url); });
    } catch(e) {}
    _pmIndex = 0;

    /* Afficher l'image courante */
    pmShowPhoto(0);

    /* Miniatures */
    const thumbs = document.getElementById('pmThumbs');
    thumbs.innerHTML = '';
    _pmPhotos.forEach((url, i) => {
        const t = document.createElement('img');
        t.src = url; t.alt = ''; t.className = 'pm-thumb' + (i === 0 ? ' active' : '');
        t.onclick = () => pmShowPhoto(i);
        thumbs.appendChild(t);
    });

    /* Infos */
    document.getElementById('pmName').textContent  = d.prodName;
    document.getElementById('pmPrice').textContent = d.prodPrice || '';
    document.getElementById('pmDesc').textContent  = d.prodDesc  || '';

    /* Stock */
    const stockEl = document.getElementById('pmStock');
    const stock   = d.prodStock !== undefined && d.prodStock !== '' ? parseInt(d.prodStock) : null;
    if (stock === null) {
        stockEl.textContent = '∞ Stock illimité'; stockEl.className = 'prod-modal-stock';
    } else if (stock === 0) {
        stockEl.textContent = '✕ Rupture de stock'; stockEl.className = 'prod-modal-stock out';
    } else if (stock <= 5) {
        stockEl.textContent = `⚠ Plus que ${stock} en stock`; stockEl.className = 'prod-modal-stock low';
    } else {
        stockEl.textContent = `✓ ${stock} en stock`; stockEl.className = 'prod-modal-stock';
    }

    document.getElementById('prodModalOverlay').classList.add('open');
}

function pmShowPhoto(index) {
    _pmIndex = Math.max(0, Math.min(index, _pmPhotos.length - 1));
    const img = document.getElementById('pmImg');
    const ph  = document.getElementById('pmImgPh');
    const arL = document.getElementById('pmArrowL');
    const arR = document.getElementById('pmArrowR');
    const ctr = document.getElementById('pmCounter');

    if (_pmPhotos.length > 0) {
        img.src = _pmPhotos[_pmIndex]; img.alt = '';
        img.style.display = ''; ph.style.display = 'none';
    } else {
        img.style.display = 'none'; ph.style.display = 'flex';
    }

    /* Flèches et compteur */
    if (_pmPhotos.length > 1) {
        arL.style.display = arR.style.display = ctr.style.display = '';
        ctr.textContent = `${_pmIndex + 1} / ${_pmPhotos.length}`;
    } else {
        arL.style.display = arR.style.display = ctr.style.display = 'none';
    }

    /* Miniature active */
    document.querySelectorAll('.pm-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === _pmIndex);
    });
}

function pmNav(dir) {
    pmShowPhoto((_pmIndex + dir + _pmPhotos.length) % _pmPhotos.length);
}

function closeProdModal() {
    document.getElementById('prodModalOverlay').classList.remove('open');
}

/* Clavier dans le modal */
document.addEventListener('keydown', e => {
    const open = document.getElementById('prodModalOverlay').classList.contains('open');
    if (e.key === 'Escape' && open)      closeProdModal();
    if (e.key === 'ArrowLeft'  && open)  pmNav(-1);
    if (e.key === 'ArrowRight' && open)  pmNav(1);
});

/* ── Marquer comme lu ── */
async function markRead() {
    try {
        await fetch('/boutique/messages/read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ client_id: _clientId, product_id: _productId }),
        });
    } catch(e) {}
}

/* ── Charger conversation ── */
async function loadConv() {
    try {
        let url = `/boutique/messages/conversation?client_id=${_clientId}`;
        if (_productId) url += `&product_id=${_productId}`;

        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const data = await res.json();
        const msgs = data.messages || [];

        const thread = document.getElementById('hubThread');
        thread.innerHTML = '';

        if (!msgs.length) {
            thread.innerHTML = '<div class="hub-thread-empty">Aucun message pour le moment.</div>';
            _lastMsgId = 0;
            return;
        }

        let lastDate = '';
        msgs.forEach(msg => {
            const dk = msg.dateKey || '';
            if (dk && dk !== lastDate) {
                thread.appendChild(buildDateSep(msg.date || dk));
                lastDate = dk;
            }
            thread.appendChild(buildRow(msg));
        });

        _lastMsgId = Math.max(...msgs.map(m => m.id || 0));
        thread.scrollTop = thread.scrollHeight;
    } catch(e) {}
}

/* ── Polling ── */
function startPoll() { stopPoll(); _pollTimer = setInterval(pollConv, 3000); }
function stopPoll()  { clearInterval(_pollTimer); _pollTimer = null; }

async function pollConv() {
    if (!_clientId) return;
    try {
        let url = `/boutique/messages/conversation?client_id=${_clientId}`;
        if (_productId) url += `&product_id=${_productId}`;

        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const data = await res.json();
        const msgs = data.messages || [];
        const thread = document.getElementById('hubThread');
        const newMsgs = msgs.filter(m => m.id > _lastMsgId);

        for (const msg of newMsgs) {
            _lastMsgId = Math.max(_lastMsgId, msg.id);
            if (thread.querySelector(`[data-msg-id="${msg.id}"]`)) continue;
            if (msg.mine) continue;
            if (msg.type && msg.type !== 'text') { await loadConv(); return; }

            const empty = thread.querySelector('.hub-thread-empty');
            if (empty) empty.remove();
            thread.appendChild(buildRow(msg));
            thread.scrollTop = thread.scrollHeight;

            /* Update sidebar preview */
            if (_convEl) {
                const p = _convEl.querySelector('.hub-conv-preview');
                if (p) p.textContent = (msg.body || '').substring(0, 40);
                const t = _convEl.querySelector('.hub-conv-time');
                if (t) t.textContent = 'À l\'instant';
                /* Move to top of list */
                const list = document.getElementById('convList');
                list.prepend(_convEl);
            }
        }
    } catch(e) {}
}

/* ── Envoyer un message ── */
async function sendHubMsg() {
    if (!_clientId) return;
    const input = document.getElementById('hubInput');
    const body  = input.value.trim();
    if (!body) return;

    const btn = document.getElementById('hubSendBtn');
    btn.disabled = true;

    try {
        const url = `/boutique/messages/reply/${_clientId}/${_productId ?? 0}`;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ body, client_id: _clientId, product_id: _productId }),
        });
        const data = await res.json();

        if (data.sent || data.success) {
            input.value = '';
            input.style.height = 'auto';
            if (data.message_id) _lastMsgId = Math.max(_lastMsgId, data.message_id);

            const thread = document.getElementById('hubThread');
            const empty  = thread.querySelector('.hub-thread-empty');
            if (empty) empty.remove();

            const now  = new Date();
            const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
            thread.appendChild(buildRow({ id: data.message_id || 0, mine: true, body, time, read: false, type: 'text' }));
            thread.scrollTop = thread.scrollHeight;

            if (_convEl) {
                const p = _convEl.querySelector('.hub-conv-preview');
                if (p) p.textContent = body.substring(0, 40);
                const t = _convEl.querySelector('.hub-conv-time');
                if (t) t.textContent = 'À l\'instant';
                document.getElementById('convList').prepend(_convEl);
            }
        } else {
            showToast('❌ Erreur lors de l\'envoi', 'err');
        }
    } catch(e) {
        showToast('❌ Erreur réseau', 'err');
    } finally {
        btn.disabled = false;
        document.getElementById('hubInput').focus();
    }
}

/* ── Offer panel ── */
function showOfferPanel(proposedPrice, proposalMsgId) {
    _offerProposalMsgId = proposalMsgId || null;
    const panel = document.getElementById('hubOfferPanel');
    const hint  = document.getElementById('hubOfferHint');
    const input = document.getElementById('hubOfferInput');
    const d     = _currentProdData;

    if (proposedPrice) {
        hint.textContent = `Le client propose ${fmtPrice(proposedPrice, _currentDevise)}. Entrez votre contre-offre :`;
        input.value = proposedPrice;
    } else {
        const raw = parseFloat(d.prodPriceRaw || 0);
        hint.textContent = raw ? `Prix catalogue : ${fmtPrice(raw, _currentDevise)}` : '';
        input.value = raw || '';
    }

    panel.classList.add('open');
    input.focus();
}

function closeOfferPanel() {
    document.getElementById('hubOfferPanel').classList.remove('open');
    document.getElementById('hubOfferInput').value = '';
    _offerProposalMsgId = null;
}

async function submitOffer() {
    if (!_clientId || !_productId) return showToast('Sélectionnez une conversation', 'err');
    const price = parseFloat(document.getElementById('hubOfferInput').value);
    if (!price || price <= 0) return showToast('Entrez un prix valide', 'err');

    const btn = document.querySelector('.hub-offer-submit');
    btn.disabled = true; btn.textContent = '⏳…';

    try {
        const body = { client_id: _clientId, product_id: _productId, offered_price: price };
        if (_offerProposalMsgId) body.proposal_message_id = _offerProposalMsgId;

        const res  = await fetch('/boutique/messages/price-offer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            showToast('✅ Offre envoyée au client !', 'ok');
            closeOfferPanel();
            await loadConv();
        } else {
            showToast('❌ ' + (data.message || 'Erreur'), 'err');
        }
    } catch(e) {
        showToast('❌ Erreur réseau', 'err');
    } finally {
        btn.disabled = false; btn.textContent = 'Envoyer ✓';
    }
}

async function refuseProposal(msgId, btn) {
    if (!confirm('Refuser cette proposition de prix ?')) return;
    if (btn) { btn.disabled = true; btn.textContent = '⏳…'; }

    try {
        const res  = await fetch(`/boutique/messages/refuse-proposal/${msgId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({}),
        });
        const data = await res.json();

        if (data.success) {
            showToast('✅ Proposition refusée', 'ok');
            await loadConv();
        } else {
            showToast('❌ ' + (data.message || 'Erreur'), 'err');
            if (btn) { btn.disabled = false; btn.textContent = 'Refuser'; }
        }
    } catch(e) {
        showToast('❌ Erreur réseau', 'err');
        if (btn) { btn.disabled = false; btn.textContent = 'Refuser'; }
    }
}

/* ── Formatage prix ── */
function fmtPrice(n, devise) {
    if (!n) return '';
    try {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n) + ' ' + (devise || _currentDevise || 'GNF');
    } catch(e) {
        return n.toLocaleString() + ' ' + (devise || 'GNF');
    }
}

/* ── Builders ── */
function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildDateSep(label) {
    const d = document.createElement('div');
    d.className = 'hub-date-sep';
    d.innerHTML = `<span>${escHtml(label)}</span>`;
    return d;
}

function buildRow(msg) {
    const type = msg.type || 'text';
    if (type === 'price_proposal') return buildProposalCard(msg);
    if (type === 'price_offer')    return buildOfferCard(msg);
    if (type === 'order_created')  return buildOrderCard(msg);
    return buildTextRow(msg);
}

function buildTextRow(msg) {
    const row = document.createElement('div');
    row.className = 'hub-msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) row.dataset.msgId = msg.id;
    const tick = msg.mine ? `<span class="hub-msg-tick">${msg.read ? '✓✓' : '✓'}</span>` : '';
    row.innerHTML = `<div class="hub-msg-bubble">${escHtml(msg.body)}<div class="hub-msg-meta"><span class="hub-msg-time">${escHtml(msg.time || '')}</span>${tick}</div></div>`;
    return row;
}

/* Proposition du client → carte jaune + boutons Accepter/Refuser */
function buildProposalCard(msg) {
    const status   = msg.proposal_status || 'pending';
    const price    = msg.proposed_price ? fmtPrice(msg.proposed_price, _currentDevise) : '—';
    const isPending = status === 'pending';

    const statusLabels = { pending: '⏳ En attente', accepted: '✅ Acceptée', refused: '❌ Refusée' };
    const statusClass  = { pending: 'pending', accepted: 'accepted', refused: 'refused' };

    const actionsHtml = isPending ? `
        <div class="nego-card-actions">
            <button class="nego-card-btn nego-btn-accept"
                    onclick="showOfferPanel(${msg.proposed_price || 0}, ${msg.id})">
                ✓ Accepter & contre-offrir
            </button>
            <button class="nego-card-btn nego-btn-refuse"
                    onclick="refuseProposal(${msg.id}, this)">
                ✕ Refuser
            </button>
        </div>` : '';

    const wrap = document.createElement('div');
    wrap.className = 'hub-msg-row theirs';
    if (msg.id) wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
        <div class="nego-card nego-proposal">
            <div class="nego-card-head">💰 Proposition de prix — client</div>
            <div class="nego-card-body">
                <span class="nego-status ${statusClass[status] || 'pending'}">${statusLabels[status] || status}</span>
                <div class="nego-card-price">${escHtml(price)}</div>
                <div class="nego-card-note">${escHtml(msg.body || '')}</div>
            </div>
            ${actionsHtml}
            <div style="padding:0 14px 8px;font-size:10.5px;color:#9ca3af">${escHtml(msg.time || '')}</div>
        </div>`;
    return wrap;
}

/* Offre du vendeur → carte verte en lecture seule */
function buildOfferCard(msg) {
    const price    = msg.proposed_price ? fmtPrice(msg.proposed_price, _currentDevise) : '—';
    const status   = msg.proposal_status || 'pending';
    const statusLabels = { pending: '⏳ En attente de confirmation', accepted: '✅ Confirmée par le client', refused: '❌ Refusée' };

    const wrap = document.createElement('div');
    wrap.className = 'hub-msg-row mine';
    if (msg.id) wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
        <div class="nego-card nego-offer">
            <div class="nego-card-head">🏷️ Votre offre de prix</div>
            <div class="nego-card-body">
                <span class="nego-status ${status}">${statusLabels[status] || status}</span>
                <div class="nego-card-price">${escHtml(price)}</div>
                <div class="nego-card-note">${escHtml(msg.body || '')}</div>
            </div>
            <div style="padding:0 14px 8px;font-size:10.5px;color:#9ca3af;text-align:right">${escHtml(msg.time || '')} <span style="color:#53bdeb">${msg.read ? '✓✓' : '✓'}</span></div>
        </div>`;
    return wrap;
}

/* Commande confirmée → carte bleue */
function buildOrderCard(msg) {
    const price = msg.proposed_price ? fmtPrice(msg.proposed_price, _currentDevise) : '—';

    const wrap = document.createElement('div');
    wrap.className = 'hub-msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
        <div class="nego-card nego-order">
            <div class="nego-card-head">🎉 Commande confirmée !</div>
            <div class="nego-card-body">
                <span class="nego-status confirmed">✅ Commande créée</span>
                <div class="nego-card-price">${escHtml(price)}</div>
                <div class="nego-card-note">${escHtml(msg.body || '')}</div>
            </div>
            <div style="padding:0 14px 8px;font-size:10.5px;color:#9ca3af">${escHtml(msg.time || '')}</div>
        </div>`;
    return wrap;
}
</script>
</body>
</html>
