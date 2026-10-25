{{--
    Mode nuit du panneau admin (tableau de bord superadmin et pages admin). À inclure en DERNIER,
    après le style de la page (et le thème Shopio). Activé par un bouton data-dark-toggle
    (admin/_partials/dark-toggle) : le choix est gardé dans le navigateur (localStorage
    shopio_dark_mode), comme dans l'espace vendeur, et posé sur <html data-theme="dark">.

    Les couleurs claires écrites en dur dans les pages ont été remplacées par ces variables :
    en mode jour elles gardent exactement leur valeur d'avant.
--}}
<style id="shopio-admin-dark">
:root{
    --ok-ink:#065F46;--err-ink:#7F1D1D;--warn-ink:#92400E;--info-ink:#1D4ED8;
    --rbd:#FECACA;--gbd:#BBF7D0;
}
[data-theme="dark"]{
    color-scheme:dark;
    --brand:#1F7CF6;--blt:#1F7CF6;--bdk:#1668D8;--glow:rgba(31,124,246,.35);
    --bg:#0A1426;--card:#10203B;--bd:#22355A;--border:#22355A;
    --text:#EAF0FA;--text2:#C3CEE0;--muted:#9DAAC2;
    --b50:#13284B;--b100:#1B3563;--bbg:#13284B;--ibg:#1B2C4A;--sky:#7DB4FF;
    --green:#4ADE80;--gbg:#0F2A1C;
    --amber:#F59E0B;--abg:#2E2208;--aink:#FBBF24;
    --red:#F87171;--rbg:#2A1214;
    --blue:#7DB4FF;--rose:#F87171;--rosebg:#2A1214;--teal:#4ADE80;--tealbg:#0F2A1C;--indigo:#7DB4FF;
    --ok-ink:#4ADE80;--err-ink:#FCA5A5;--warn-ink:#FBBF24;--info-ink:#7DB4FF;
    --rbd:#5B2328;--gbd:#1E4D33;
    --sb:#07152B;--sb2:#132B52;--sb-line:#1C3560;
}
[data-theme="dark"] body{background:var(--bg);color:var(--text)}

/* Menu un peu plus sombre que la page, pour rester distinct */
[data-theme="dark"] .sb{background:var(--sb);border-right:1px solid var(--sb-line)}

/* Barre du haut, menus déroulants, panneau de notifications */
[data-theme="dark"] .tb{background:var(--card);border-bottom-color:var(--bd)}
[data-theme="dark"] .drop,[data-theme="dark"] .np{background:var(--card);border-color:var(--bd)}
[data-theme="dark"] .ndot,[data-theme="dark"] .bell-badge{border-color:var(--card)}

/* Champs de saisie et listes déroulantes */
[data-theme="dark"] input:not([type=checkbox]):not([type=radio]):not([type=submit]),
[data-theme="dark"] select,[data-theme="dark"] textarea{background:var(--bg);color:var(--text);border-color:var(--bd)}
[data-theme="dark"] input::placeholder,[data-theme="dark"] textarea::placeholder{color:var(--muted)}

/* Ombres plus discrètes, survols lisibles */
[data-theme="dark"] .kpi:hover,[data-theme="dark"] .stat-c:hover{box-shadow:0 14px 28px -18px rgba(0,0,0,.7)}
[data-theme="dark"] .tbl tbody tr:hover,[data-theme="dark"] tr:hover td{background:rgba(255,255,255,.03)}
[data-theme="dark"] .qa:hover{background:var(--b50)}

/* Éléments en bleu nuit : ils resteraient invisibles sur le fond sombre */
[data-theme="dark"] .saas-card.livr .saas-num-v.main{color:var(--text)}
[data-theme="dark"] .kpi-ic.i,[data-theme="dark"] .stat-ico.liv{color:var(--sky)}
[data-theme="dark"] .rev-card.ca,[data-theme="dark"] #notifSettingsCard{background:var(--sb2)!important}
[data-theme="dark"] .rev-card.ca .rev-tag{background:var(--b100)}

/* Tableau de bord : badge blanc de la carte bleue, fenêtre de confirmation */
[data-theme="dark"] .rev-card.today .rev-badge{background:#fff;color:#1260C9}
[data-theme="dark"] #purgeModal>div,[data-theme="dark"] #staffModal>div{background:var(--card)!important;color:var(--text)}

/* Bouton lune / soleil */
.tb-dark{width:40px;height:40px;flex-shrink:0;margin-left:auto;border-radius:999px;border:0;cursor:pointer;
    display:inline-flex;align-items:center;justify-content:center;background:var(--card);color:var(--text2);
    box-shadow:inset 0 0 0 1.5px var(--bd);transition:background-color .15s,color .15s}
.tb-dark:hover{background:var(--b50);color:var(--brand)}
.tb-dark:focus-visible{outline:3px solid var(--blt);outline-offset:2px}
.tb-acts .tb-dark{margin-left:0}
</style>
