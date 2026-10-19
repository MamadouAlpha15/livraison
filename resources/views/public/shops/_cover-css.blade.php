{{--
    Styles de l'en-tête « couverture » de la vitrine (Faire grandir ma vitrine → Couverture et logo).
    Partagés par la vitrine (public/shops/products) et par l'aperçu de l'écran vendeur (vendeur/shop/grow) :
    à inclure à l'intérieur d'une balise <style>.
    Variables posées sur .sf-cover : --lg0 (taille du logo), --cv-y (cadrage vertical), --cv-veil (voile, 0 à .7).
    Classe .sf-cv-phone : rendu téléphone forcé (aperçu vendeur), comme sous 760 px.
--}}
.sf-cover { --lg: var(--lg0, 104px); --cv-h: 380px; --cv-pad: 24px; position: relative; background: var(--surface, #fff); box-shadow: 0 1px 0 var(--border, #E3E8F2); font-family: var(--font, 'Plus Jakarta Sans', system-ui, sans-serif); }
.sf-cover.sf-cover-h-m { --cv-h: 300px; }
.sf-cover.sf-cover-h-s { --cv-h: 200px; }
.sf-cover-media { position: relative; height: var(--cv-h); overflow: hidden; background: #0B1E3D; border-bottom: 4px solid var(--sf-accent, #1F7CF6); }
.sf-cover-slide { position: absolute; inset: 0; opacity: 0; background-size: cover; background-position: center; transition: opacity .8s cubic-bezier(.22,1,.36,1); }
.sf-cover-slide.is-on { opacity: 1; }
.sf-cover-slide img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: 50% var(--cv-y, 50%); }
.sf-cover-veil { position: absolute; inset: 0; pointer-events: none; background: linear-gradient(180deg, rgba(6,18,38,calc(var(--cv-veil, .3) * .55)) 0%, rgba(6,18,38,var(--cv-veil, .3)) 100%); }
/* Nom et slogan écrits sur la couverture */
.sf-cover-text { position: absolute; left: calc(var(--cv-pad) + var(--lg) + 18px); right: var(--cv-pad); bottom: 16px; color: #fff; text-shadow: 0 2px 14px rgba(0,0,0,.45); }
.sf-logo-center .sf-cover-text { left: var(--cv-pad); text-align: center; bottom: calc(var(--lg) / 2 + 14px); }
.sf-cover-name { margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -.02em; line-height: 1.12; text-wrap: balance; overflow-wrap: anywhere; }
.sf-cover-slogan { margin: 6px 0 0; font-size: 15px; font-weight: 600; opacity: .95; overflow-wrap: anywhere; }
.sf-cover-dots { position: absolute; right: var(--cv-pad); bottom: 14px; display: flex; gap: 6px; z-index: 2; }
.sf-cover-dots button { width: 9px; height: 9px; padding: 0; border: 0; border-radius: 999px; background: rgba(255,255,255,.5); cursor: pointer; transition: width .26s cubic-bezier(.22,1,.36,1), background-color .18s; }
.sf-cover-dots button[aria-current="true"] { width: 24px; background: #fff; }
.sf-logo-center .sf-cover-dots { bottom: auto; top: 14px; }
/* Logo : chevauche le bas de la couverture de la moitié de sa hauteur, anneau blanc de 4 px */
.sf-cover-logo { position: absolute; z-index: 3; left: var(--cv-pad); top: calc(var(--cv-h) - var(--lg) / 2 + 2px); width: var(--lg); height: var(--lg); border: 4px solid #fff; border-radius: 50%; background: #fff; overflow: hidden; box-shadow: 0 12px 28px rgba(11,30,61,.28); display: flex; align-items: center; justify-content: center; }
.sf-logo-rounded .sf-cover-logo { border-radius: 24%; }
.sf-logo-square .sf-cover-logo { border-radius: 8px; }
.sf-logo-center .sf-cover-logo { left: 50%; transform: translateX(-50%); }
.sf-logo-over .sf-cover-logo { top: calc(var(--cv-h) - var(--lg) - 18px); }
.sf-cover-logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sf-cover-initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--sf-accent, #1F7CF6); color: var(--sf-ink, #fff); font-weight: 800; font-size: calc(var(--lg) * .36); letter-spacing: -.02em; }
/* Barre sous la couverture : nom (s'il n'est pas sur la photo), slogan, preuves et infos */
.sf-cover-bar { padding: 14px var(--cv-pad) 18px calc(var(--cv-pad) + var(--lg) + 18px); min-height: calc(var(--lg) / 2 + 22px); display: flex; flex-direction: column; gap: 8px; }
.sf-logo-center .sf-cover-bar { padding: calc(var(--lg) / 2 + 16px) var(--cv-pad) 18px; align-items: center; text-align: center; }
.sf-logo-over .sf-cover-bar { padding-left: var(--cv-pad); min-height: 0; }
.sf-cover-title { display: flex; align-items: center; gap: 8px 10px; flex-wrap: wrap; }
.sf-logo-center .sf-cover-title { justify-content: center; }
.sf-cover-title .sf-cover-name { font-size: 26px; color: var(--text, #16162A); }
.sf-cover-sub { margin: 0; color: var(--text-2, #45455E); font-size: 15px; font-weight: 600; }
.sf-cover-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.sf-logo-center .sf-cover-meta { justify-content: center; }
.sf-cv-chip { display: inline-flex; align-items: center; gap: 5px; height: 28px; padding: 0 11px; border-radius: 999px; background: var(--grey-2, #EEF2F8); color: var(--text-2, #45455E); font-size: 12.5px; font-weight: 700; white-space: nowrap; }
.sf-cv-chip.is-verified { background: #E7F6EC; color: #15803D; }
.sf-cv-chip.is-proof { background: #EAF2FF; color: #1260C9; }
.sf-cv-chip .ico, .sf-cv-chip svg { flex-shrink: 0; }
.sf-cover .sf-status.is-open { background: #E7F6EC; color: #15803D; }
.sf-cover .sf-status.is-closed { background: var(--grey-2, #EEF2F8); color: var(--text-2, #45455E); }
/* Téléphone (et aperçu téléphone de l'écran vendeur) */
@media (max-width: 760px) {
    .sf-cover { --lg: calc(var(--lg0, 104px) * .7); --cv-h: 230px; --cv-pad: 16px; }
    .sf-cover.sf-cover-h-m { --cv-h: 180px; }
    .sf-cover.sf-cover-h-s { --cv-h: 130px; }
    .sf-cover-name { font-size: 21px; }
    .sf-cover-title .sf-cover-name { font-size: 20px; }
    .sf-cover-slogan, .sf-cover-sub { font-size: 13.5px; }
    .sf-cover-text { left: calc(var(--cv-pad) + var(--lg) + 12px); bottom: 12px; }
    .sf-cover-bar { padding-left: calc(var(--cv-pad) + var(--lg) + 12px); }
}
.sf-cv-phone.sf-cover { --lg: calc(var(--lg0, 104px) * .7); --cv-h: 230px; --cv-pad: 16px; }
.sf-cv-phone.sf-cover.sf-cover-h-m { --cv-h: 180px; }
.sf-cv-phone.sf-cover.sf-cover-h-s { --cv-h: 130px; }
.sf-cv-phone .sf-cover-name { font-size: 21px; }
.sf-cv-phone .sf-cover-title .sf-cover-name { font-size: 20px; }
.sf-cv-phone .sf-cover-slogan, .sf-cv-phone .sf-cover-sub { font-size: 13.5px; }
.sf-cv-phone .sf-cover-text { left: calc(var(--cv-pad) + var(--lg) + 12px); bottom: 12px; }
.sf-cv-phone .sf-cover-bar { padding-left: calc(var(--cv-pad) + var(--lg) + 12px); }
.sf-cv-phone.sf-logo-center .sf-cover-text { left: var(--cv-pad); bottom: calc(var(--lg) / 2 + 12px); }
.sf-cv-phone.sf-logo-center .sf-cover-bar, .sf-cv-phone.sf-logo-over .sf-cover-bar { padding-left: var(--cv-pad); }
@media (max-width: 760px) {
    .sf-logo-center .sf-cover-text { left: var(--cv-pad); bottom: calc(var(--lg) / 2 + 12px); }
    .sf-logo-center .sf-cover-bar, .sf-logo-over .sf-cover-bar { padding-left: var(--cv-pad); }
}
@media (prefers-reduced-motion: reduce) {
    .sf-cover-slide, .sf-cover-dots button { transition: none; }
}
