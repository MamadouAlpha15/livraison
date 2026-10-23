/* ══════════════════════════════════════════
   MODALE PROFIL — OUVERTURE / FERMETURE
══════════════════════════════════════════ */
function openProfileModal(tab) {
    document.getElementById('profileOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    switchProfileTab(tab || 'info');
}
function closeProfileModal() {
    document.getElementById('profileOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

/* ── Pagination classement ── */
const RANK_PER_PAGE = 10;
let _rankPage = 0;

function _rankRender() {
    const rows = document.querySelectorAll('#rankModalBody .rank-row');
    const total = rows.length;
    const pages = Math.ceil(total / RANK_PER_PAGE);
    const start = _rankPage * RANK_PER_PAGE;
    const end   = start + RANK_PER_PAGE;
    rows.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });
    const foot = document.getElementById('rankModalFoot');
    const info = document.getElementById('rankPgInfo');
    const prev = document.getElementById('rankPgPrev');
    const next = document.getElementById('rankPgNext');
    if (total > RANK_PER_PAGE) {
        foot.style.display = '';
        info.textContent   = `Page ${_rankPage + 1} / ${pages}  ·  ${total} boutiques`;
        prev.disabled = _rankPage === 0;
        next.disabled = _rankPage >= pages - 1;
    } else {
        foot.style.display = 'none';
    }
    document.getElementById('rankModalBody').scrollTop = 0;
}

function rankChangePage(dir) {
    const total = document.querySelectorAll('#rankModalBody .rank-row').length;
    const pages = Math.ceil(total / RANK_PER_PAGE);
    _rankPage = Math.max(0, Math.min(pages - 1, _rankPage + dir));
    _rankRender();
}

function openRankingModal() {
    const el = document.getElementById('rankOverlay');
    if (!el) return;
    _rankPage = 0;
    _rankRender();
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeRankingModal() {
    const el = document.getElementById('rankOverlay');
    if (!el) return;
    el.classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRankingModal(); });

/* ── Onglets ── */
function switchProfileTab(name) {
    ['info','pwd','del'].forEach(function(t) {
        var body = document.getElementById('ptab-' + t + '-body');
        var btn  = document.getElementById('ptab-' + t);
        if (!body || !btn) return;
        var active = (t === name);
        body.style.display  = active ? 'block' : 'none';
        btn.style.background = active ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.12)';
        btn.style.color      = active ? '#1668D8' : 'rgba(255,255,255,.75)';
        btn.style.fontWeight = active ? '800' : '700';
        btn.style.boxShadow  = active ? '0 2px 8px rgba(0,0,0,.12)' : 'none';
    });
}

/* ── Drapeau pays ── */
function updateModalFlag(select) {
    var opt  = select.options[select.selectedIndex];
    var text = opt.textContent.trim();
    var flag = text.split(' ')[0];
    var fp = document.getElementById('modalFlagPreview');
    if (flag && flag !== '--') fp.textContent = flag; else fp.innerHTML = SIco.globe || '';
}

/* ── Bascule visibilité mot de passe ── */
function togglePwd(inputId, btn) {
    var inp = document.getElementById(inputId);
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.innerHTML = inp.type === 'password' ? SIco.eye : SIco['eye-off'];
    btn.setAttribute('aria-label', inp.type === 'password' ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
}

/* ── Force mot de passe ── */
function evalPwdStrength(val) {
    var bar   = document.getElementById('pwdStrengthBar');
    var label = document.getElementById('pwdStrengthLabel');
    if (!bar || !label) return;
    if (!val) { bar.style.width = '0%'; label.textContent = ''; return; }
    var score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var levels = [
        { pct:'20%', color:'#ef4444', text:'Très faible' },
        { pct:'40%', color:'#f97316', text:'Faible' },
        { pct:'60%', color:'#eab308', text:'Moyen' },
        { pct:'80%', color:'#22c55e', text:'Fort' },
        { pct:'100%',color:'#15803d', text:'Très fort' },
    ];
    var lv = levels[Math.max(0, score - 1)] || levels[0];
    bar.style.width      = lv.pct;
    bar.style.background = lv.color;
    label.textContent    = lv.text;
    label.style.color    = lv.color;
}

/* ── Activer/désactiver le bouton suppression ── */
function toggleDelBtn() {
    var checked = document.getElementById('delConfirmCheck').checked;
    var btn     = document.getElementById('delBtn');
    btn.disabled              = !checked;
    btn.style.background      = checked ? 'linear-gradient(135deg,#ef4444,#dc2626)' : '#d1d5db';
    btn.style.cursor          = checked ? 'pointer' : 'not-allowed';
    btn.style.boxShadow       = checked ? '0 4px 14px rgba(239,68,68,.4)' : 'none';
}

/* ── Confirmation avant suppression ── */
function confirmDel() {
    var pwd = document.getElementById('delPassword').value.trim();
    if (!pwd) {
        alert('Veuillez saisir votre mot de passe pour confirmer.');
        return false;
    }
    return confirm('Êtes-vous absolument certain de vouloir supprimer votre compte ? Cette action est irréversible.');
}

/* ══════════════════════════════════════════
   AUTO-OUVERTURE MODALE PROFIL (erreurs / status)
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    @if(session('status') === 'profile-updated')
        openProfileModal('info');
        if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Modifié avec succès !', text: 'Vos informations ont bien été mises à jour.', confirmButtonColor: '#1668D8' });
        }
    @endif
    @if(session('status') === 'password-updated')
        if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Modifié avec succès !', text: 'Votre mot de passe a bien été changé.', confirmButtonColor: '#1668D8' });
        }
    @endif
    @if($errors->updatePassword->any())
        openProfileModal('pwd');
    @endif
    @if($errors->userDeletion->any())
        openProfileModal('del');
    @endif
});
