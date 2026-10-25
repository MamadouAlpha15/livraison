{{--
    Thème Shopio du panneau admin (superadmin / staff). À inclure APRÈS le <style> propre à la page :
    il redéfinit les couleurs (variables), la police et l'apparence des éléments communes à toutes
    les pages admin (menu, barre du haut, cartes, tableaux, filtres, badges, boutons), sans toucher
    à leur disposition. Même apparence que le tableau de bord (dashboards/SuperAdmin).
--}}
<style id="shopio-admin-theme">
:root{
    --brand:#1668D8;--blt:#1F7CF6;--bdk:#1260C9;--glow:rgba(22,104,216,.28);
    --sb:#0B1E3D;--sb2:#132B52;--sb-line:#1C3560;--sb-text:#C9D6EA;--sb-mut:#8FA6CC;
    --bg:#F6F9FF;--card:#fff;--bd:#E3E8F2;--border:#E3E8F2;
    --text:#16162A;--text2:#45455E;--muted:#62627C;
    --b50:#EAF2FF;--b100:#D6E6FF;--sky:#7DB4FF;
    --green:#15803D;--gbg:#E7F6EC;
    --amber:#F59E0B;--abg:#FEF3E0;--aink:#92400E;
    --red:#B91C1C;--rbg:#FDECEC;
    --blue:#1F7CF6;--bbg:#EAF2FF;
    --indigo:#132B52;--ibg:#E6ECF5;
    --font:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text)}

/* ── Menu de gauche ── */
.sb{background:var(--sb);scrollbar-color:var(--sb-line) transparent}
.sb::-webkit-scrollbar-thumb{background:var(--sb-line)}
.sb-logo{border-bottom:1px solid var(--sb-line);gap:12px}
.sb-ico-wrap{width:42px;height:42px;border-radius:12px;background:var(--blt);box-shadow:none;overflow:hidden}
.sb-ico-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.sb-appname{font-size:17px;font-weight:800;color:#fff}
.sb-apptag{font-size:11px;font-weight:600;color:var(--sb-mut);text-transform:none;letter-spacing:0}
.sb-me{margin:14px 14px 4px;padding:12px;border-radius:16px;background:var(--sb2);border-bottom:0}
.sb-av{background:var(--blt);border:0;box-shadow:none;border-radius:999px}
.sb-name{font-size:14px;font-weight:800;color:#fff}
.sb-badge,.sb-badge-role{display:inline-block;font-size:11px;font-weight:600;color:var(--sb-mut);background:none;border:0;padding:0;margin-top:2px;text-transform:none;letter-spacing:0}
.sb-nav{padding:6px 14px 10px;display:flex;flex-direction:column;gap:2px}
.sb-sec,.sb-sec.shop,.sb-sec.livr,.sb-sec.fin,.sb-sec.plat{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--sb-mut);padding:16px 10px 6px}
.sb-a{gap:12px;padding:10px 12px;color:var(--sb-text);font-size:14px;font-weight:600;border-left:0;border-radius:12px;box-shadow:none}
.sb-a:hover{background:var(--sb2);color:#fff}
.sb-a.on{background:var(--blt);color:#fff;font-weight:700;border-left:0;box-shadow:0 8px 20px -10px rgba(31,124,246,.7)}
.sb-a:focus-visible{outline:3px solid var(--sky);outline-offset:2px}
.sb-i{opacity:.9}
.sb-pill{font-size:11px;font-weight:800;border-radius:999px}
.sb-pill.a{background:var(--amber);color:#16162A}.sb-pill.r{background:#F87171;color:#fff}.sb-pill.g{background:#4ADE80;color:#16162A}
.sb-ft{border-top:1px solid var(--sb-line);background:none;padding-left:14px;padding-right:14px}
.sb-ft-row{border-radius:12px;color:var(--sb-text);font-size:13px}
.sb-ft-row:hover{background:var(--sb2);color:#fff}
.sb-ov{background:rgba(11,30,61,.5);backdrop-filter:none}
.sb-close{border-radius:999px;background:var(--sb2);color:var(--sb-text)}
.live-dot{background:#4ADE80}

/* ── Barre du haut ── */
.tb{background:var(--card);border-bottom:1px solid var(--bd)}
.tb-ttl{color:var(--text);font-weight:800}.tb-ttl b{color:var(--brand)}
.ham{border-radius:999px;color:var(--text2)}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-btn{border-radius:999px;border-color:var(--bd);color:var(--text2)}
.tb-btn:hover{background:var(--b50);color:var(--brand)}
.tb-user{border-radius:999px;border-color:var(--bd);font-family:var(--font)}
.tb-uav{background:var(--blt);border-radius:999px}
.drop{border-radius:16px;border-color:var(--bd);box-shadow:0 16px 40px -12px rgba(11,30,61,.3)}
.drop-i{border-radius:10px;font-weight:600}
.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.live-badge{color:var(--green);background:var(--gbg);border:0;border-radius:999px}

/* ── Contenu ── */
.flash{border-radius:16px;font-weight:700}
.flash.ok{background:var(--gbg);color:var(--green);border:0}
.flash.err{background:var(--rbg);color:var(--red);border:0}
.ph h1{font-weight:800;letter-spacing:-.02em;color:var(--text)}
.ph-sub{color:var(--text2)}

/* Indicateurs */
.kpi{border-radius:20px;border-color:var(--bd)}
.kpi:hover{box-shadow:0 14px 28px -18px rgba(11,30,61,.35)}
.kpi::before{display:none}
.kpi-ic{border-radius:14px}
.kpi-ic.p,.kpi-ic.b{background:var(--b50);color:var(--brand)}
.kpi-ic.i{background:var(--ibg);color:var(--sb2)}
.kpi-ic.g,.kpi-ic.t{background:var(--gbg);color:var(--green)}
.kpi-ic.a{background:var(--abg);color:var(--aink)}
.kpi-ic.r,.kpi-ic.ro{background:var(--rbg);color:var(--red)}
.kpi-v{font-weight:800;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
.kpi-l{color:var(--text2)}

/* Cartes et tableaux */
.sc{border-radius:20px;border-color:var(--bd);box-shadow:none}
.sc-t{font-weight:700}
/* Police Shopio un peu plus large : cellules un peu moins espacées pour garder toutes les colonnes visibles */
.tbl th{color:var(--muted);background:var(--bg);border-bottom-color:var(--bd);padding-left:12px;padding-right:12px}
.tbl td{border-bottom-color:var(--bd);padding-left:12px;padding-right:12px}
.tbl tbody tr:hover{background:var(--bg)}
.pag{border-top-color:var(--bd)}

/* Filtres */
.chip{border-radius:999px;border-color:var(--bd);color:var(--text2)}
.chip:hover{border-color:var(--sky);color:var(--brand)}
.chip.on{background:var(--b50);border-color:var(--brand);color:var(--brand)}
.fb-sel,.fb-inp{border-radius:12px;border-color:var(--bd)}
.fb-sel:focus,.fb-inp:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(31,124,246,.15)}
.fb-btn{border-radius:999px;border-color:var(--bd);color:var(--text2)}
.fb-btn:hover{background:var(--b50);color:var(--brand)}
.fb-btn.on{background:var(--b50);border-color:var(--brand);color:var(--brand)}

/* Badges et boutons */
.bdg{border-radius:999px}
.bdg.p,.bdg.b{color:var(--bdk);background:var(--b50)}
.bdg.m{color:var(--text2);background:var(--bg)}
.btn-p{border-radius:999px;background:var(--brand)}
.btn-p:hover{background:var(--bdk);box-shadow:0 8px 20px -8px var(--glow)}
.btn-g{border-radius:999px;background:var(--card);color:var(--text);border-color:var(--bd)}
.btn-g:hover{background:var(--card);border-color:var(--blt);color:var(--brand)}
.btn-sm{border-radius:999px}
.btn-p:focus-visible,.btn-g:focus-visible,.btn-sm:focus-visible,.fb-btn:focus-visible,.chip:focus-visible,.tb-btn:focus-visible{outline:3px solid var(--blt);outline-offset:2px}
</style>
