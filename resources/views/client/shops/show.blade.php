{{--
    resources/views/client/shops/show.blade.php
--}}
@extends('layouts.app')
@section('title', $shop->name . ' — Produits')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --amazon:    #f90;
    --amazon-dk: #e47911;
    --amazon-lt: #fff8e7;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --blue:      #007185;
    --green:     #067d62;
    --green-lt:  #e8f5e9;
    --red:       #b12704;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --text-2:    #333;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Noto Sans', sans-serif;
    --r:         8px;
    --r-sm:      4px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,.12);
    --shadow:    0 4px 16px rgba(0,0,0,.14);
    --nav-h:     60px;
}
html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

/* ══ NAVBAR ══ */
.amz-nav {
    background: var(--navy); height: var(--nav-h);
    display: flex; align-items: center; padding: 0 16px; gap: 10px;
    position: sticky; top: 0; z-index: 100;
}
.amz-nav-logo { font-size: 20px; font-weight: 900; color: var(--amazon); text-decoration: none; flex-shrink: 0; }
.amz-nav-logo span { color: #fff; }
.amz-back { display: flex; align-items: center; gap: 5px; color: rgba(255,255,255,.85); font-size: 13px; font-weight: 600; text-decoration: none; padding: 6px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; flex-shrink: 0; }
.amz-back:hover { border-color: rgba(255,255,255,.5); color: #fff; }
.amz-nav-search { flex: 1; display: flex; border-radius: var(--r-sm); overflow: hidden; border: 2px solid var(--amazon); max-width: 600px; min-width: 0; }
.amz-nav-search input { flex: 1; border: none; outline: none; padding: 9px 12px; font-size: 14px; font-family: var(--font); background: var(--surface); color: var(--text); min-width: 0; }
.amz-nav-search-btn { background: var(--amazon); border: none; padding: 0 14px; cursor: pointer; font-size: 15px; transition: background .15s; flex-shrink: 0; }
.amz-nav-search-btn:hover { background: var(--amazon-dk); }
.amz-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.amz-nav-link { color: rgba(255,255,255,.85); font-size: 12px; text-decoration: none; padding: 6px 8px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; }
.amz-nav-link:hover { border-color: rgba(255,255,255,.5); color: #fff; }
.amz-nav-link strong { display: block; font-size: 13px; color: #fff; }

/* ══ BANNIÈRE ══ */
.shop-banner {
    background: var(--navy-2); padding: 14px 20px;
    display: flex; align-items: center; gap: 14px;
    border-bottom: 3px solid var(--amazon);
}
.shop-banner-logo { width: 60px; height: 60px; border-radius: var(--r); background: var(--surface); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; overflow: hidden; border: 2px solid rgba(255,255,255,.15); }
.shop-banner-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-banner-info { flex: 1; min-width: 0; }
.shop-banner-name { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.shop-banner-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.shop-banner-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: rgba(255,255,255,.65); }
.shop-banner-open { display: inline-flex; align-items: center; gap: 5px; background: var(--green); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.shop-banner-open::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #a8f0d4; animation: pulse 1.8s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ══ BOUTON FILTRES MOBILE ══ */
.btn-filter-mobile {
    display: none;
    align-items: center; gap: 8px;
    padding: 9px 16px; background: var(--surface);
    border: 1px solid var(--border); border-radius: var(--r);
    font-size: 13px; font-weight: 700; color: var(--text);
    cursor: pointer; margin-bottom: 12px; width: 100%;
    font-family: var(--font);
}
.btn-filter-mobile:hover { background: var(--amazon-lt); border-color: var(--amazon); }

/* Drawer sidebar mobile */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; }
.sidebar-overlay.open { display: block; }

/* ══ LAYOUT ══ */
.amz-layout { max-width: 1500px; margin: 0 auto; display: flex; gap: 0; padding: 16px 16px 60px; }

/* ══ SIDEBAR ══ */
.amz-sidebar {
    width: 220px; flex-shrink: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 16px;
    height: fit-content; position: sticky; top: 76px;
    box-shadow: var(--shadow-sm); margin-right: 16px;
}
.amz-sidebar-title { font-size: 16px; font-weight: 700; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; }
.amz-sidebar-close { display: none; background: none; border: none; font-size: 18px; cursor: pointer; color: var(--muted); padding: 0; }
.amz-sidebar-section { margin-bottom: 18px; }
.amz-sidebar-section-title { font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--grey-2); }
.amz-filter-item { display: flex; align-items: center; gap: 8px; padding: 5px 0; cursor: pointer; }
.amz-filter-item input[type=radio] { accent-color: var(--amazon); cursor: pointer; flex-shrink: 0; }
.amz-filter-item label { font-size: 13px; color: var(--blue); cursor: pointer; transition: color .12s; }
.amz-filter-item:hover label { color: var(--amazon-dk); text-decoration: underline; }
.amz-filter-item.active-filter label { color: var(--text); font-weight: 700; }
.amz-price-range { display: flex; gap: 6px; align-items: center; }
.amz-price-input { flex: 1; padding: 6px 8px; border: 1px solid var(--border); border-radius: var(--r-sm); font-size: 12px; font-family: var(--font); color: var(--text); outline: none; min-width: 0; }
.amz-price-input:focus { border-color: var(--amazon); }
.amz-price-btn { padding: 6px 10px; background: var(--grey-2); border: 1px solid var(--border); border-radius: var(--r-sm); font-size: 12px; font-family: var(--font); cursor: pointer; transition: background .15s; color: var(--text); width: 100%; margin-top: 6px; }
.amz-price-btn:hover { background: var(--amazon-lt); border-color: var(--amazon); }

/* ══ CONTENU ══ */
.amz-content { flex: 1; min-width: 0; }

/* Barre résultats */
.amz-results-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 10px 14px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; margin-bottom: 14px; box-shadow: var(--shadow-sm); flex-wrap: wrap;
}
.amz-results-text { font-size: 13px; color: var(--muted); }
.amz-results-text strong { color: var(--text); font-weight: 700; }
.amz-sort-wrap { display: flex; align-items: center; gap: 6px; }
.amz-sort-label { font-size: 13px; color: var(--muted); white-space: nowrap; }
.amz-sort-select { padding: 6px 8px; border: 1px solid var(--border); border-radius: var(--r-sm); font-size: 12px; font-family: var(--font); color: var(--text); background: var(--surface); outline: none; cursor: pointer; max-width: 140px; }
.amz-sort-select:focus { border-color: var(--amazon); }
.amz-view-btns { display: flex; gap: 4px; }
.amz-view-btn { padding: 6px 10px; border: 1px solid var(--border); background: var(--surface); border-radius: var(--r-sm); cursor: pointer; font-size: 14px; transition: all .15s; color: var(--muted); }
.amz-view-btn.active { background: var(--amazon-lt); border-color: var(--amazon); color: var(--amazon-dk); }

/* ══ GRILLE ══ */
.amz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; }
.amz-grid.list-view { grid-template-columns: 1fr; }
.amz-grid.list-view .amz-card { flex-direction: row; min-height: 150px; }
.amz-grid.list-view .amz-card-img { width: 150px; height: 150px; flex-shrink: 0; }
.amz-grid.list-view .amz-card-body { flex: 1; padding: 12px 16px; }
.amz-grid.list-view .amz-card-footer { border-top: none; border-left: 1px solid var(--border); width: 160px; flex-shrink: 0; padding: 12px; display: flex; flex-direction: column; justify-content: center; gap: 8px; }

/* ══ CARD ══ */
.amz-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .2s, border-color .2s; display: flex; flex-direction: column; position: relative; }
.amz-card:hover { box-shadow: var(--shadow); border-color: #aaa; }
.amz-card-img { height: 190px; overflow: hidden; position: relative; background: #f9f9f9; flex-shrink: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.amz-card-img img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform .3s; padding: 8px; }
.amz-card:hover .amz-card-img img { transform: scale(1.04); }
.amz-card-img-ph { font-size: 44px; opacity: .25; }
.amz-card-badge { position: absolute; top: 8px; left: 8px; display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; padding: 3px 7px; border-radius: 3px; white-space: nowrap; }
.badge-promo   { background: #cc0c39; color: #fff; }
.badge-nouveau { background: var(--amazon); color: var(--navy); }
.badge-vedette { background: var(--navy-2); color: var(--amazon); }
.badge-rupture { background: #888; color: #fff; }
.amz-quick-view { position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,.65); color: #fff; border: none; border-radius: 4px; padding: 5px 10px; font-size: 11px; font-weight: 700; cursor: pointer; opacity: 0; transition: opacity .2s; font-family: var(--font); }
.amz-card:hover .amz-quick-view { opacity: 1; }
.amz-card-body { padding: 10px 12px 6px; flex: 1; display: flex; flex-direction: column; gap: 5px; }
.amz-card-cat { font-size: 11px; color: var(--blue); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.amz-card-name { font-size: 13px; color: var(--text); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; font-weight: 500; cursor: pointer; }
.amz-card-name:hover { color: var(--amazon-dk); }
.amz-stars { display: flex; align-items: center; gap: 4px; }
.amz-stars-ico { color: var(--amazon); font-size: 12px; letter-spacing: 1px; }
.amz-stars-count { font-size: 11px; color: var(--blue); }
.amz-price-wrap { margin-top: 4px; }
.amz-price-main { font-size: 18px; font-weight: 700; color: var(--red); letter-spacing: -.5px; }
.amz-price-devise { font-size: 11px; color: var(--muted); font-weight: 400; }
.amz-price-orig { font-size: 11px; color: var(--muted); text-decoration: line-through; }
.amz-price-remise { font-size: 11px; color: var(--red); font-weight: 600; }
.amz-delivery { font-size: 11px; color: var(--green); }
.amz-stock-ok  { font-size: 11px; color: var(--green); font-weight: 600; }
.amz-stock-low { font-size: 11px; color: var(--amazon-dk); font-weight: 600; }
.amz-stock-out { font-size: 11px; color: var(--red); font-weight: 600; }
.amz-card-footer { padding: 10px 12px; border-top: 1px solid var(--grey-2); }
.amz-btn-order { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 9px 10px; border-radius: 20px; font-size: 12.5px; font-weight: 700; font-family: var(--font); background: var(--amazon); color: var(--navy); border: 1px solid var(--amazon-dk); cursor: pointer; text-decoration: none; transition: all .15s; margin-bottom: 6px; }
.amz-btn-order:hover { background: var(--amazon-dk); color: #fff; }
.amz-btn-order.out { background: var(--grey-2); color: var(--muted); border-color: var(--border); cursor: not-allowed; }
.amz-btn-msg { display: flex; align-items: center; justify-content: center; gap: 5px; width: 100%; padding: 7px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; font-family: var(--font); background: var(--surface); color: var(--text); border: 1px solid var(--border); cursor: pointer; text-decoration: none; transition: all .15s; }
.amz-btn-msg:hover { background: var(--grey-2); border-color: #999; }

/* ══ FLASH ══ */
.amz-flash { padding: 10px 14px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.amz-flash-success { background: var(--green-lt); border-color: #6ee7b7; color: #065f46; }
.amz-flash-danger  { background: #fff5f5; border-color: #fca5a5; color: var(--red); }
.amz-empty { grid-column: 1/-1; padding: 64px 20px; text-align: center; background: var(--surface); border-radius: var(--r); border: 1px solid var(--border); }
.amz-pagination { display: flex; justify-content: center; padding: 20px 0 8px; }

/* ══════════════════════════
   MODAL DÉTAIL PRODUIT
══════════════════════════ */
.prod-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,.65); align-items: center; justify-content: center;
    padding: 12px;
}
.prod-modal-overlay.open { display: flex; }

.prod-modal {
    background: var(--surface); border-radius: 12px;
    max-width: 920px; width: 100%; max-height: 94vh;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 80px rgba(0,0,0,.3);
    animation: modalSlide .22s ease;
    overflow: hidden;
}
@keyframes modalSlide { from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)} }

.prod-modal-hd {
    padding: 12px 18px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--grey); flex-shrink: 0;
}
.prod-modal-hd-shop { font-size: 13px; color: var(--muted); display: flex; align-items: center; gap: 6px; }
.prod-modal-hd-shop strong { color: var(--text); }
.prod-modal-close { width: 32px; height: 32px; border-radius: 50%; background: var(--grey-2); border: 1px solid var(--border); color: var(--text); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.prod-modal-close:hover { background: #fce4e4; border-color: #fca5a5; color: var(--red); }

.prod-modal-body { display: flex; flex: 1; overflow: hidden; min-height: 0; }

/* Galerie */
.prod-modal-gallery { width: 360px; flex-shrink: 0; padding: 18px; display: flex; flex-direction: column; gap: 10px; border-right: 1px solid var(--border); background: #fafafa; overflow-y: auto; }
.prod-modal-main-img { width: 100%; height: 300px; border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; background: var(--surface); display: flex; align-items: center; justify-content: center; cursor: zoom-in; position: relative; flex-shrink: 0; }
.prod-modal-main-img img { max-width: 100%; max-height: 100%; object-fit: contain; padding: 10px; transition: transform .3s; }
.prod-modal-main-img:hover img { transform: scale(1.06); }
.prod-modal-main-img-ph { font-size: 60px; opacity: .2; }
.prod-modal-badge { position: absolute; top: 10px; left: 10px; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 4px; }
.prod-modal-thumbs { display: flex; gap: 8px; flex-wrap: wrap; }
.prod-modal-thumb { width: 60px; height: 60px; border-radius: var(--r-sm); border: 2.5px solid transparent; overflow: hidden; cursor: pointer; transition: all .15s; opacity: .6; background: var(--surface); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.prod-modal-thumb img { width: 100%; height: 100%; object-fit: contain; padding: 3px; }
.prod-modal-thumb:hover { opacity: .9; border-color: #aaa; }
.prod-modal-thumb.active { opacity: 1; border-color: var(--amazon); transform: scale(1.05); }

/* Infos produit */
.prod-modal-info { flex: 1; overflow-y: auto; padding: 18px 22px; min-width: 0; }
.prod-modal-info::-webkit-scrollbar { width: 5px; }
.prod-modal-info::-webkit-scrollbar-thumb { background: var(--amazon); border-radius: 5px; }

.prod-modal-cat { font-size: 11px; color: var(--blue); font-weight: 700; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px; }
.prod-modal-name { font-size: 19px; font-weight: 700; color: var(--text); line-height: 1.3; margin-bottom: 10px; }
.prod-modal-stars { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.prod-modal-stars-ico { color: var(--amazon); font-size: 14px; letter-spacing: 2px; }
.prod-modal-stars-txt { font-size: 13px; color: var(--blue); }
.prod-modal-price-wrap { margin-bottom: 12px; }
.prod-modal-price-lbl { font-size: 12px; color: var(--muted); margin-bottom: 3px; }
.prod-modal-price { font-size: 26px; font-weight: 700; color: var(--red); display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
.prod-modal-devise { font-size: 13px; color: var(--muted); font-weight: 400; }
.prod-modal-orig { font-size: 14px; color: var(--muted); text-decoration: line-through; }
.prod-modal-remise { font-size: 12px; font-weight: 700; background: var(--red); color: #fff; padding: 2px 8px; border-radius: 3px; }
.prod-modal-save { font-size: 13px; color: var(--red); font-weight: 600; margin-top: 2px; }
.prod-modal-desc-title { font-size: 13px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; }
.prod-modal-desc { font-size: 13px; color: var(--text-2); line-height: 1.7; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.prod-modal-specs { margin-bottom: 14px; }
.prod-modal-spec-row { display: flex; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--grey-2); font-size: 13px; }
.prod-modal-spec-key { width: 120px; flex-shrink: 0; color: var(--muted); font-weight: 600; }
.prod-modal-spec-val { color: var(--text-2); flex: 1; min-width: 0; }
.prod-modal-stock-ok  { font-size: 14px; color: var(--green); font-weight: 700; margin-bottom: 12px; }
.prod-modal-stock-low { font-size: 13px; color: var(--amazon-dk); font-weight: 700; margin-bottom: 12px; }
.prod-modal-stock-out { font-size: 14px; color: var(--red); font-weight: 700; margin-bottom: 12px; }

/* Footer modal */
.prod-modal-ft { padding: 12px 18px; border-top: 1px solid var(--border); background: var(--grey); display: flex; gap: 10px; flex-wrap: wrap; flex-shrink: 0; }
.prod-modal-btn-order { flex: 1; min-width: 140px; padding: 11px 14px; border-radius: 20px; font-size: 13.5px; font-weight: 700; font-family: var(--font); background: var(--amazon); color: var(--navy); border: 1px solid var(--amazon-dk); cursor: pointer; text-decoration: none; transition: all .15s; display: flex; align-items: center; justify-content: center; gap: 6px; }
.prod-modal-btn-order:hover { background: var(--amazon-dk); color: #fff; }
.prod-modal-btn-order.out { background: var(--grey-2); color: var(--muted); border-color: var(--border); cursor: not-allowed; }
.prod-modal-btn-msg { flex: 0 0 auto; padding: 11px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; font-family: var(--font); background: var(--surface); color: var(--text); border: 1px solid var(--border); cursor: pointer; text-decoration: none; transition: all .15s; display: flex; align-items: center; gap: 6px; }
.prod-modal-btn-msg:hover { background: var(--grey-2); border-color: #999; }

/* Fullscreen */
.fs-overlay { display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,.96); align-items: center; justify-content: center; }
.fs-overlay.open { display: flex; }
.fs-overlay img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: var(--r); }
.fs-close { position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.fs-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 46px; height: 46px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
.fs-nav:hover { background: rgba(255,255,255,.2); }
.fs-prev { left: 12px; }
.fs-next { right: 12px; }
.fs-counter { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.5); color: rgba(255,255,255,.8); font-size: 12px; padding: 4px 14px; border-radius: 20px; font-family: monospace; }

/* ══════════════════════════════════════════
   RESPONSIVE COMPLET
══════════════════════════════════════════ */

/* Tablette large — sidebar cachée, bouton filtre visible */
@media (max-width: 960px) {
    .amz-sidebar {
        display: none;
        position: fixed; top: 0; left: 0; bottom: 0;
        width: 280px; z-index: 300; border-radius: 0;
        height: 100vh; overflow-y: auto;
        transform: translateX(-100%);
        transition: transform .25s ease;
        margin-right: 0;
    }
    .amz-sidebar.open {
        display: block;
        transform: translateX(0);
    }
    .amz-sidebar-close { display: block; }
    .btn-filter-mobile { display: flex; }
    .amz-layout { padding: 12px 12px 50px; }
}

/* Tablette */
@media (max-width: 768px) {
    .amz-nav-right { display: none; }
    .amz-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
    /* Modal : galerie en haut, infos en bas */
    .prod-modal { max-height: 98vh; }
    .prod-modal-body { flex-direction: column; overflow-y: auto; }
    .prod-modal-gallery { width: 100%; border-right: none; border-bottom: 1px solid var(--border); padding: 14px; }
    .prod-modal-main-img { height: 260px; }
    .prod-modal-info { overflow-y: visible; padding: 14px 16px; }
    .prod-modal-price { font-size: 22px; }
    .prod-modal-name { font-size: 17px; }
    .prod-modal-thumbs { justify-content: center; }
}

/* Mobile grand */
@media (max-width: 600px) {
    .amz-nav { padding: 0 10px; gap: 8px; }
    .amz-nav-logo { font-size: 17px; }
    .amz-back { font-size: 12px; padding: 5px 8px; }
    .amz-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .amz-card-img { height: 150px; }
    .amz-card-name { font-size: 12px; -webkit-line-clamp: 2; }
    .amz-price-main { font-size: 15px; }
    .amz-card-body { padding: 8px 10px 4px; gap: 4px; }
    .amz-card-footer { padding: 8px 10px; }
    .amz-btn-order { font-size: 11.5px; padding: 8px 8px; }
    .amz-btn-msg { font-size: 11px; padding: 6px 8px; }
    .shop-banner { padding: 10px 12px; gap: 10px; }
    .shop-banner-logo { width: 44px; height: 44px; font-size: 20px; }
    .shop-banner-name { font-size: 15px; }
    .shop-banner-chip { font-size: 11px; }
    /* Modal bottom sheet sur mobile */
    .prod-modal-overlay { padding: 0; align-items: flex-end; }
    .prod-modal { border-radius: 16px 16px 0 0; max-height: 96vh; }
    .prod-modal-main-img { height: 220px; }
    .prod-modal-name { font-size: 16px; }
    .prod-modal-price { font-size: 20px; }
    .prod-modal-ft { gap: 8px; padding: 10px 14px; }
    .prod-modal-btn-order { font-size: 13px; padding: 10px 12px; }
    .prod-modal-btn-msg { font-size: 12px; padding: 10px 12px; }
    .amz-results-bar { padding: 8px 10px; gap: 8px; }
    .amz-sort-label { display: none; }
    .amz-sort-select { font-size: 11.5px; max-width: 120px; }
}

/* Mobile petit */
@media (max-width: 400px) {
    .amz-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    .amz-card-img { height: 130px; }
    .amz-card-name { font-size: 11.5px; }
    .amz-price-main { font-size: 14px; }
    .amz-btn-order { font-size: 11px; padding: 7px 6px; }
    .amz-btn-msg { display: none; } /* caché sur très petit écran */
    .shop-banner-name { font-size: 13px; }
    .amz-layout { padding: 8px 8px 40px; }
    .prod-modal-main-img { height: 180px; }
    .prod-modal-thumb { width: 50px; height: 50px; }
}

/* Swipe tactile modal */
@media (hover: none) {
    .amz-quick-view { opacity: 1; }
}
</style>
@endpush

@section('content')
@php $devise = $shop->currency ?? 'GNF'; @endphp
@php $bodyClass = 'is-dashboard'; @endphp

{{-- MODAL DÉTAIL PRODUIT --}}
<div class="prod-modal-overlay" id="prodModal" onclick="if(event.target===this)closeModal()">
    <div class="prod-modal">
        <div class="prod-modal-hd">
            <div class="prod-modal-hd-shop">🛍️ &nbsp;<strong>{{ $shop->name }}</strong></div>
            <button class="prod-modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="prod-modal-body">
            <div class="prod-modal-gallery">
                <div class="prod-modal-main-img" id="modalMainWrap" onclick="openFs()">
                    <img src="" id="modalMainImg" alt="">
                    <div class="prod-modal-main-img-ph" id="modalMainPh" style="display:none">🏷️</div>
                    <div class="prod-modal-badge badge-promo" id="modalBadge" style="display:none"></div>
                </div>
                <div class="prod-modal-thumbs" id="modalThumbs"></div>
            </div>
            <div class="prod-modal-info">
                <div class="prod-modal-cat" id="modalCat"></div>
                <div class="prod-modal-name" id="modalName"></div>
                <div class="prod-modal-stars">
                    <span class="prod-modal-stars-ico">★★★★★</span>
                    <span class="prod-modal-stars-txt" id="modalStars"></span>
                </div>
                <div class="prod-modal-price-wrap">
                    <div class="prod-modal-price-lbl">Prix</div>
                    <div class="prod-modal-price">
                        <span id="modalPrice"></span>
                        <span class="prod-modal-devise" id="modalDevise">{{ $devise }}</span>
                        <span class="prod-modal-orig" id="modalOrig" style="display:none"></span>
                        <span class="prod-modal-remise" id="modalRemise" style="display:none"></span>
                    </div>
                    <div class="prod-modal-save" id="modalSave" style="display:none"></div>
                </div>
                <div id="modalStockWrap" style="margin-bottom:12px"></div>
                <div id="modalDesc" style="display:none">
                    <div class="prod-modal-desc-title">À propos de ce produit</div>
                    <div class="prod-modal-desc" id="modalDescText"></div>
                </div>
                <div class="prod-modal-specs" id="modalSpecs"></div>
                <div style="font-size:13px;color:var(--green);display:flex;align-items:center;gap:6px;margin-top:4px">
                    ✓ Livraison disponible — paiement cash à la réception
                </div>
            </div>
        </div>
        <div class="prod-modal-ft">
            <a href="#" class="prod-modal-btn-order" id="modalBtnOrder">🛒 Commander maintenant</a>
            <a href="#" class="prod-modal-btn-msg" id="modalBtnMsg">💬 Poser une question</a>
        </div>
    </div>
</div>

{{-- Fullscreen --}}
<div class="fs-overlay" id="fsOverlay" onclick="if(event.target===this)closeFsOverlay()">
    <button class="fs-close" onclick="closeFsOverlay()">✕</button>
    <button class="fs-nav fs-prev" id="fsPrev" onclick="fsNav(-1)">‹</button>
    <img id="fsImg" src="" alt="">
    <button class="fs-nav fs-next" id="fsNext" onclick="fsNav(1)">›</button>
    <div class="fs-counter" id="fsCounter"></div>
</div>

{{-- Overlay sidebar mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- NAVBAR --}}
<nav class="amz-nav">
    <a href="{{ route('client.dashboard') }}" class="amz-nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.dashboard') }}" class="amz-back">← Retour</a>
    <div class="amz-nav-search">
        <input type="text" id="prodSearch" placeholder="Rechercher dans {{ $shop->name }}…">
        <button class="amz-nav-search-btn" onclick="doSearch()">🔍</button>
    </div>
    <div class="amz-nav-right">
        <a href="{{ route('client.orders.index') }}" class="amz-nav-link">
            <span>Retours</span><strong>& Commandes</strong>
        </a>
    </div>
</nav>

{{-- BANNIÈRE --}}
<div class="shop-banner">
    <div class="shop-banner-logo">
        @if($shop->image)
            <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }}"
                 alt="{{ $shop->name }}" fetchpriority="high" decoding="async" width="80" height="80">
        @else 🛍️ @endif
    </div>
    <div class="shop-banner-info">
        <div class="shop-banner-name">{{ $shop->name }}</div>
        <div class="shop-banner-meta">
            <span class="shop-banner-open">Ouvert</span>
            @if($shop->type)<span class="shop-banner-chip">🏷️ {{ $shop->type }}</span>@endif
            @if($shop->address)<span class="shop-banner-chip">📍 {{ Str::limit($shop->address,28) }}</span>@endif
            <span class="shop-banner-chip">📦 {{ $products->total() }} produit{{ $products->total()>1?'s':'' }}</span>
            @if($shop->phone)<span class="shop-banner-chip">📞 {{ $shop->phone }}</span>@endif
        </div>
    </div>
</div>

{{-- LAYOUT --}}
<div class="amz-layout">

    {{-- Sidebar --}}
    <aside class="amz-sidebar" id="sidebarEl">
        <div class="amz-sidebar-title">
            Affiner
            <button class="amz-sidebar-close" onclick="closeSidebar()">✕</button>
        </div>

        @if(!empty($categories) && $categories->count())
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Catégorie</div>
            <div class="amz-filter-item active-filter" id="cat-item-all" onclick="filterCat('',this)">
                <input type="radio" name="cat" id="cat-all" checked>
                <label for="cat-all">Tous les produits</label>
            </div>
            @foreach($categories as $cat)
            <div class="amz-filter-item" id="cat-item-{{ Str::slug($cat) }}" onclick="filterCat('{{ strtolower($cat) }}',this)">
                <input type="radio" name="cat" id="cat-{{ Str::slug($cat) }}">
                <label for="cat-{{ Str::slug($cat) }}">{{ $cat }}</label>
            </div>
            @endforeach
        </div>
        @endif

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Disponibilité</div>
            <div class="amz-filter-item active-filter" id="stock-item-all" onclick="filterStock(false,this)">
                <input type="radio" name="stock" id="stock-all" checked><label for="stock-all">Tous</label>
            </div>
            <div class="amz-filter-item" id="stock-item-in" onclick="filterStock(true,this)">
                <input type="radio" name="stock" id="stock-avail"><label for="stock-avail">En stock uniquement</label>
            </div>
        </div>

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Prix</div>
            <div class="amz-price-range">
                <input type="number" class="amz-price-input" id="priceMin" placeholder="Min">
                <span style="color:var(--muted);font-size:12px">—</span>
                <input type="number" class="amz-price-input" id="priceMax" placeholder="Max">
            </div>
            <button class="amz-price-btn" onclick="filterPrice()">Appliquer</button>
            <button class="amz-price-btn" style="background:var(--grey)" onclick="resetPrice()">Réinitialiser</button>
        </div>

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Trier par</div>
            @foreach([['default','Pertinence'],['price_asc','Prix ↑'],['price_desc','Prix ↓'],['featured','Vedettes']] as [$val,$lbl])
            <div class="amz-filter-item {{ $val==='default'?'active-filter':'' }}" id="sort-item-{{ $val }}" onclick="setSort('{{ $val }}',this)">
                <input type="radio" name="sort" id="sort-{{ $val }}" {{ $val==='default'?'checked':'' }}>
                <label for="sort-{{ $val }}">{{ $lbl }}</label>
            </div>
            @endforeach
        </div>
    </aside>

    {{-- Contenu --}}
    <div class="amz-content">

        @foreach(['success','danger'] as $t)
            @if(session($t))<div class="amz-flash amz-flash-{{ $t }}">{{ session($t) }}</div>@endif
        @endforeach

        {{-- Bouton filtres mobile --}}
        <button class="btn-filter-mobile" onclick="openSidebar()">
            ⚙️ Filtres & Tri
            <span id="activeFiltersCount" style="background:var(--amazon);color:var(--navy);border-radius:20px;padding:1px 8px;font-size:11px;display:none">0</span>
        </button>

        <div class="amz-results-bar">
            <div class="amz-results-text">
                <strong id="resultCount">{{ $products->total() }}</strong> résultat{{ $products->total()>1?'s':'' }} dans <strong>{{ $shop->name }}</strong>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <div class="amz-sort-wrap">
                    <span class="amz-sort-label">Trier :</span>
                    <select class="amz-sort-select" id="sortSelectTop" onchange="setSort(this.value,null)">
                        <option value="default">Pertinence</option>
                        <option value="price_asc">Prix ↑</option>
                        <option value="price_desc">Prix ↓</option>
                        <option value="name">Nom A→Z</option>
                        <option value="featured">Vedettes</option>
                    </select>
                </div>
                <div class="amz-view-btns">
                    <button class="amz-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grille">⊞</button>
                    <button class="amz-view-btn" id="btnList" onclick="setView('list')" title="Liste">≡</button>
                </div>
            </div>
        </div>

        <div class="amz-grid" id="prodGrid">
            @forelse($products as $product)
            @php
                $hasPromo  = $product->original_price && $product->original_price > $product->price;
                $remise    = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
                $stockVal  = $product->stock ?? null;
                $stockOut  = $stockVal !== null && $stockVal <= 0;
                $stockLow  = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
                $isNew     = $product->created_at->diffInDays(now()) <= 7;
                $gallery   = json_decode($product->gallery ?? '[]', true) ?: [];
                $allPhotos = array_values(array_filter(array_merge(
                    $product->image ? [asset('storage/'.$product->image)] : [],
                    array_map(fn($g) => asset('storage/'.$g), $gallery)
                )));
                $prodData  = json_encode([
                    'id'       => $product->id,
                    'name'     => $product->name,
                    'cat'      => $product->category ?? '',
                    'price'    => $product->price,
                    'orig'     => $product->original_price,
                    'remise'   => $remise,
                    'hasPromo' => $hasPromo,
                    'desc'     => $product->description ?? '',
                    'unit'     => $product->unit ?? '',
                    'prep'     => $product->preparation_time ?? '',
                    'stock'    => $stockVal,
                    'stockOut' => $stockOut,
                    'stockLow' => $stockLow,
                    'featured' => (bool)$product->is_featured,
                    'photos'   => $allPhotos,
                    'orderUrl' => route('client.orders.createFromProduct', $product),
                    'msgUrl'   => route('client.messages.index', $product),
                    'devise'   => $shop->currency ?? 'GNF',
                ]);
            @endphp
            <div class="amz-card"
                 data-name="{{ strtolower($product->name) }}"
                 data-cat="{{ strtolower($product->category ?? '') }}"
                 data-price="{{ $product->price }}"
                 data-featured="{{ $product->is_featured ? '1' : '0' }}"
                 data-stock="{{ $stockOut ? 'out' : 'in' }}"
                 data-prod='{{ $prodData }}'>

                <div class="amz-card-img" onclick='openModal(JSON.parse(this.closest(".amz-card").dataset.prod))'>
                    @if($product->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') }}"
                             srcset="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') }} 300w,
                                     {{ \App\Services\ImageOptimizer::url($product->image, 'medium') }} 800w"
                             sizes="(max-width:600px) 45vw, 200px"
                             alt="{{ $product->name }}" loading="lazy" decoding="async" width="200" height="200">
                    @else
                        <div class="amz-card-img-ph">🏷️</div>
                    @endif
                    @if($hasPromo)<span class="amz-card-badge badge-promo">-{{ $remise }}%</span>
                    @elseif($isNew)<span class="amz-card-badge badge-nouveau">Nouveau</span>
                    @elseif($product->is_featured)<span class="amz-card-badge badge-vedette">⭐</span>
                    @elseif($stockOut)<span class="amz-card-badge badge-rupture">Rupture</span>@endif
                    <button class="amz-quick-view" onclick='event.stopPropagation();openModal(JSON.parse(this.closest(".amz-card").dataset.prod))'>👁 Détails</button>
                </div>

                <div class="amz-card-body">
                    @if($product->category)<div class="amz-card-cat">{{ $product->category }}</div>@endif
                    <div class="amz-card-name" onclick='openModal(JSON.parse(this.closest(".amz-card").dataset.prod))'>{{ $product->name }}</div>
                    <div class="amz-stars">
                        <span class="amz-stars-ico">★★★★★</span>
                        <span class="amz-stars-count">({{ rand(10,200) }})</span>
                    </div>
                    <div class="amz-price-wrap">
                        <div style="display:flex;align-items:baseline;gap:5px;flex-wrap:wrap">
                            <span class="amz-price-main">{{ number_format($product->price,0,',',' ') }} <span class="amz-price-devise">{{ $devise }}</span></span>
                            @if($hasPromo)<span class="amz-price-orig">{{ number_format($product->original_price,0,',',' ') }}</span><span class="amz-price-remise">-{{ $remise }}%</span>@endif
                        </div>
                        <div class="amz-delivery">✓ Livraison dispo</div>
                    </div>
                    @if($stockVal !== null)
                        @if($stockOut)<div class="amz-stock-out">Rupture</div>
                        @elseif($stockLow)<div class="amz-stock-low">{{ $stockVal }} restants</div>
                        @else<div class="amz-stock-ok">En stock</div>@endif
                    @endif
                </div>

                <div class="amz-card-footer">
                    @auth
                        @if(Auth::user()->role === 'client')
                            @if(!$stockOut)
                            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order">🛒 Commander</a>
                            @else
                            <div class="amz-btn-order out">❌ Indisponible</div>
                            @endif
                            <a href="{{ route('client.messages.index', $product) }}" class="amz-btn-msg">💬 Question</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="amz-btn-order">S'inscrire</a>
                    @endauth
                </div>
            </div>
            @empty
            <div class="amz-empty">
                <div style="font-size:48px;opacity:.3;margin-bottom:12px">📭</div>
                <div style="font-size:18px;font-weight:700;margin-bottom:6px">Aucun produit</div>
                <p style="color:var(--muted);font-size:14px">Cette boutique n'a pas encore de produits.</p>
            </div>
            @endforelse
        </div>

        <div class="amz-pagination">{{ $products->links() }}</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let activeCat  = '';
let activeSort = 'default';
let stockOnly  = false;
let priceMin   = 0;
let priceMax   = Infinity;
let searchQ    = '';
let modalPhotos = [], modalIdx = 0, fsPhotos = [], fsIdx = 0;

/* ══ SIDEBAR MOBILE ══ */
function openSidebar() {
    document.getElementById('sidebarEl').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebarEl').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ══ MODAL ══ */
function openModal(prod) {
    document.getElementById('prodModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('modalCat').textContent   = prod.cat || '';
    document.getElementById('modalName').textContent  = prod.name;
    document.getElementById('modalStars').textContent = '4.5 — ' + (Math.floor(Math.random()*200)+10) + ' avis';
    document.getElementById('modalPrice').textContent = Number(prod.price).toLocaleString('fr-FR');
    document.getElementById('modalDevise').textContent = ' ' + prod.devise;
    const origEl = document.getElementById('modalOrig');
    const remEl  = document.getElementById('modalRemise');
    const saveEl = document.getElementById('modalSave');
    if (prod.hasPromo && prod.orig) {
        origEl.textContent = Number(prod.orig).toLocaleString('fr-FR') + ' ' + prod.devise;
        remEl.textContent  = '-' + prod.remise + '%';
        saveEl.textContent = 'Économie : ' + Number(prod.orig - prod.price).toLocaleString('fr-FR') + ' ' + prod.devise;
        origEl.style.display = remEl.style.display = saveEl.style.display = '';
    } else {
        origEl.style.display = remEl.style.display = saveEl.style.display = 'none';
    }
    const sw = document.getElementById('modalStockWrap');
    if (prod.stockOut)      sw.innerHTML = '<div class="prod-modal-stock-out">❌ Rupture de stock</div>';
    else if (prod.stockLow) sw.innerHTML = '<div class="prod-modal-stock-low">⚠ Plus que ' + prod.stock + ' en stock !</div>';
    else if (prod.stock !== null) sw.innerHTML = '<div class="prod-modal-stock-ok">✓ En stock</div>';
    else sw.innerHTML = '<div class="prod-modal-stock-ok">✓ Disponible</div>';
    const descWrap = document.getElementById('modalDesc');
    if (prod.desc) { document.getElementById('modalDescText').textContent = prod.desc; descWrap.style.display = ''; }
    else descWrap.style.display = 'none';
    let sh = '';
    if (prod.unit) sh += specRow('Unité', prod.unit);
    if (prod.prep) sh += specRow('Préparation', prod.prep + ' min');
    sh += specRow('Boutique', '{{ addslashes($shop->name) }}');
    sh += specRow('Livraison', '<span style="color:var(--green)">✓ Cash à la livraison</span>');
    document.getElementById('modalSpecs').innerHTML = sh ? '<div class="prod-modal-desc-title" style="margin-bottom:8px">Informations</div>' + sh : '';
    const badge = document.getElementById('modalBadge');
    if (prod.hasPromo) { badge.textContent = '-'+prod.remise+'%'; badge.className='prod-modal-badge badge-promo'; badge.style.display=''; }
    else if (prod.featured) { badge.textContent='⭐ Vedette'; badge.className='prod-modal-badge badge-vedette'; badge.style.display=''; }
    else badge.style.display = 'none';
    modalPhotos = prod.photos || []; fsPhotos = modalPhotos; modalIdx = 0;
    buildModalGallery();
    const btnOrder = document.getElementById('modalBtnOrder');
    if (prod.stockOut) { btnOrder.className='prod-modal-btn-order out'; btnOrder.removeAttribute('href'); btnOrder.textContent='❌ Indisponible'; }
    else { btnOrder.className='prod-modal-btn-order'; btnOrder.href=prod.orderUrl; btnOrder.innerHTML='🛒 Commander maintenant'; }
    document.getElementById('modalBtnMsg').href = prod.msgUrl;
}
function specRow(k,v) { return `<div class="prod-modal-spec-row"><span class="prod-modal-spec-key">${k}</span><span class="prod-modal-spec-val">${v}</span></div>`; }
function buildModalGallery() {
    const mi = document.getElementById('modalMainImg');
    const ph = document.getElementById('modalMainPh');
    const tb = document.getElementById('modalThumbs');
    if (modalPhotos.length > 0) { mi.src=modalPhotos[0]; mi.style.display=''; ph.style.display='none'; }
    else { mi.style.display='none'; ph.style.display=''; }
    tb.innerHTML = '';
    if (modalPhotos.length > 1) {
        modalPhotos.forEach((url,i) => {
            const d = document.createElement('div');
            d.className = 'prod-modal-thumb'+(i===0?' active':'');
            d.innerHTML = `<img src="${url}" alt="Photo ${i+1}" loading="lazy">`;
            d.onclick = () => switchModalPhoto(url, i, d);
            tb.appendChild(d);
        });
    }
}
function switchModalPhoto(url, idx, el) {
    document.getElementById('modalMainImg').src = url;
    document.querySelectorAll('.prod-modal-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active'); modalIdx = idx;
}
function closeModal() {
    document.getElementById('prodModal').classList.remove('open');
    document.body.style.overflow = '';
}

/* ══ FULLSCREEN ══ */
function openFs() {
    if (!modalPhotos.length) return;
    fsIdx = modalIdx; fsPhotos = modalPhotos;
    document.getElementById('fsImg').src = fsPhotos[fsIdx];
    document.getElementById('fsOverlay').classList.add('open');
    updateFsNav();
}
function closeFsOverlay() { document.getElementById('fsOverlay').classList.remove('open'); }
function fsNav(dir) { fsIdx = Math.max(0, Math.min(fsPhotos.length-1, fsIdx+dir)); document.getElementById('fsImg').src = fsPhotos[fsIdx]; updateFsNav(); }
function updateFsNav() {
    document.getElementById('fsPrev').style.display = fsIdx===0 ? 'none' : '';
    document.getElementById('fsNext').style.display = fsIdx>=fsPhotos.length-1 ? 'none' : '';
    document.getElementById('fsCounter').textContent = (fsIdx+1)+' / '+fsPhotos.length;
}

/* ══ FILTRES ══ */
function filterCat(cat, el) {
    activeCat = cat;
    document.querySelectorAll('[id^="cat-item-"]').forEach(e => e.classList.remove('active-filter'));
    if (el) el.classList.add('active-filter');
    applyFilters(); updateFiltersBadge();
}
function filterStock(only, el) {
    stockOnly = only;
    document.querySelectorAll('[id^="stock-item-"]').forEach(e => e.classList.remove('active-filter'));
    if (el) el.classList.add('active-filter');
    applyFilters(); updateFiltersBadge();
}
function filterPrice() {
    const mn = parseFloat(document.getElementById('priceMin').value);
    const mx = parseFloat(document.getElementById('priceMax').value);
    priceMin = isNaN(mn) ? 0 : mn; priceMax = isNaN(mx) ? Infinity : mx;
    applyFilters(); updateFiltersBadge();
}
function resetPrice() {
    document.getElementById('priceMin').value = '';
    document.getElementById('priceMax').value = '';
    priceMin = 0; priceMax = Infinity;
    applyFilters(); updateFiltersBadge();
}
function setSort(val, el) {
    activeSort = val;
    document.getElementById('sortSelectTop').value = val;
    document.querySelectorAll('[id^="sort-item-"]').forEach(e => e.classList.remove('active-filter'));
    if (el) el.classList.add('active-filter');
    applyFilters();
}
function setView(type) {
    const g = document.getElementById('prodGrid');
    if (type === 'list') { g.classList.add('list-view'); document.getElementById('btnList').classList.add('active'); document.getElementById('btnGrid').classList.remove('active'); }
    else { g.classList.remove('list-view'); document.getElementById('btnGrid').classList.add('active'); document.getElementById('btnList').classList.remove('active'); }
}
function doSearch() { searchQ = document.getElementById('prodSearch').value.toLowerCase().trim(); applyFilters(); }
function applyFilters() {
    const cards = Array.from(document.querySelectorAll('.amz-card'));
    let visible = cards.filter(c => {
        const n = c.dataset.name||'', ct = c.dataset.cat||'';
        const p = parseFloat(c.dataset.price)||0, s = c.dataset.stock;
        return (!activeCat||ct===activeCat) && (!searchQ||n.includes(searchQ)) && (!stockOnly||s==='in') && (p>=priceMin&&p<=priceMax);
    });
    cards.forEach(c => c.style.display = 'none');
    visible.sort((a,b) => {
        const pa=parseFloat(a.dataset.price), pb=parseFloat(b.dataset.price);
        const fa=parseInt(a.dataset.featured), fb=parseInt(b.dataset.featured);
        if (activeSort==='price_asc')  return pa-pb;
        if (activeSort==='price_desc') return pb-pa;
        if (activeSort==='name')       return (a.dataset.name||'').localeCompare(b.dataset.name||'');
        if (activeSort==='featured')   return fb-fa;
        return 0;
    });
    const grid = document.getElementById('prodGrid');
    visible.forEach(c => { c.style.display=''; grid.appendChild(c); });
    document.getElementById('resultCount').textContent = visible.length;
}
function updateFiltersBadge() {
    let n = 0;
    if (activeCat) n++;
    if (stockOnly) n++;
    if (priceMin > 0 || priceMax < Infinity) n++;
    const el = document.getElementById('activeFiltersCount');
    if (n > 0) { el.textContent = n; el.style.display = ''; }
    else el.style.display = 'none';
}

/* ══ CLAVIER ══ */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeFsOverlay(); closeModal(); closeSidebar(); }
    if (document.getElementById('fsOverlay').classList.contains('open')) {
        if (e.key === 'ArrowLeft') fsNav(-1);
        if (e.key === 'ArrowRight') fsNav(1);
    }
});

/* ══ RECHERCHE ══ */
let st;
document.getElementById('prodSearch').addEventListener('input', e => {
    clearTimeout(st);
    st = setTimeout(() => { searchQ = e.target.value.toLowerCase().trim(); applyFilters(); }, 250);
});
document.getElementById('prodSearch').addEventListener('keydown', e => {
    if (e.key === 'Enter') { clearTimeout(st); doSearch(); }
});

/* ══ SWIPE TACTILE MODAL ══ */
let touchStartX = 0;
document.getElementById('prodModal').addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
document.getElementById('prodModal').addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 60 && modalPhotos.length > 1) {
        const newIdx = Math.max(0, Math.min(modalPhotos.length-1, modalIdx + (dx < 0 ? 1 : -1)));
        if (newIdx !== modalIdx) {
            modalIdx = newIdx;
            document.getElementById('modalMainImg').src = modalPhotos[modalIdx];
            document.querySelectorAll('.prod-modal-thumb').forEach((t,i) => t.classList.toggle('active', i===modalIdx));
        }
    }
}, {passive:true});

/* ══ ANIMATIONS ══ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.amz-card').forEach((c,i) => {
        c.style.opacity = '0';
        setTimeout(() => { c.style.transition='opacity .3s ease'; c.style.opacity='1'; }, 30+i*20);
    });
});
</script>
@endpush