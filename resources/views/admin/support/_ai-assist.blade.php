{{--
    Barre « IA de Shopio » au-dessus de la zone de réponse du support (équipe) :
    « Proposer une réponse » ou « Améliorer mon texte », avec une consigne facultative.
    La proposition remplace le texte de la zone de réponse ; rien n'est envoyé sans l'admin.
    Variables : $inputId (id du textarea), $ticketJs (expression JS qui donne l'id du ticket ouvert)
--}}
<div class="ai-bar" id="aiBar">
    <div class="ai-row">
        <span class="ai-tag">{!! \App\Support\IconLibrary::svg('sparkles', '', 15) !!} IA Shopio</span>
        <button type="button" class="ai-btn" data-ai="rediger">Proposer une réponse</button>
        <button type="button" class="ai-btn" data-ai="ameliorer">Améliorer mon texte</button>
        <label class="sr-only" for="aiInstruction">Consigne pour l'IA (facultatif)</label>
        <input type="text" id="aiInstruction" class="ai-ins" maxlength="300" placeholder="Consigne (facultatif) : plus court, rassurer…">
    </div>
    <div class="ai-status" id="aiStatus" role="status" aria-live="polite" hidden></div>
</div>

<style>
.ai-bar{display:grid;gap:6px;padding:8px 16px 0;background:var(--card);border-top:1px solid var(--bd)}
.ai-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.ai-tag{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:800;color:var(--brand);white-space:nowrap}
.ai-btn{min-height:32px;padding:0 12px;border-radius:999px;border:1px solid var(--bd);background:var(--bg);color:var(--text);font:600 12.5px var(--font);cursor:pointer;white-space:nowrap;transition:border-color .13s,color .13s}
.ai-btn:hover:not(:disabled){border-color:var(--brand);color:var(--brand)}
.ai-btn:disabled{opacity:.55;cursor:wait}
.ai-btn:focus-visible,.ai-ins:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
.ai-ins{flex:1 1 220px;min-width:0;min-height:32px;padding:0 12px;border-radius:999px;border:1px solid var(--bd);background:var(--bg);color:var(--text);font:500 12.5px var(--font);outline:none}
.ai-ins::placeholder{color:var(--muted)}
.ai-status{display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12px;font-weight:600;color:var(--muted)}
.ai-status.ok{color:var(--brand)}
.ai-status.err{color:#B91C1C}
[data-theme="dark"] .ai-status.err{color:#FCA5A5}
.ai-undo{background:none;border:0;padding:0;color:inherit;font:700 12px var(--font);text-decoration:underline;cursor:pointer}
.chat-input .ai-bar{padding:0 0 8px;border-top:0;background:transparent}
.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
@media (max-width:640px){.ai-tag{display:none}.ai-btn{flex:1 1 auto}.ai-ins{flex-basis:100%}}
</style>

<script>
(function () {
    const bar = document.getElementById('aiBar');
    if (!bar) return;
    const statusEl = document.getElementById('aiStatus');
    const instruction = document.getElementById('aiInstruction');
    const input = () => document.getElementById(@json($inputId));
    const ticketId = () => { try { return {!! $ticketJs !!}; } catch (e) { return null; } };
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token());
    let previous = null, lastTicket = null;

    function status(text, tone, withUndo) {
        statusEl.hidden = !text;
        statusEl.className = 'ai-status' + (tone ? ' ' + tone : '');
        statusEl.textContent = text || '';
        if (withUndo) {
            const undo = document.createElement('button');
            undo.type = 'button'; undo.className = 'ai-undo'; undo.textContent = 'Revenir à mon texte';
            undo.addEventListener('click', () => {
                const el = input(); el.value = previous || ''; el.dispatchEvent(new Event('input')); el.focus(); status('');
            });
            statusEl.appendChild(undo);
        }
    }

    async function run(mode) {
        const el = input(), id = ticketId();
        if (!el || !id) { status('Ouvre d\'abord une conversation.', 'err'); return; }
        if (lastTicket !== id) { previous = null; lastTicket = id; }
        if (mode === 'ameliorer' && !el.value.trim()) { status('Écris d\'abord ta réponse : l\'IA l\'améliorera ensuite.', 'err'); return; }

        const buttons = bar.querySelectorAll('.ai-btn');
        buttons.forEach(b => b.disabled = true);
        status(mode === 'ameliorer' ? 'L\'IA améliore ton texte…' : 'L\'IA lit la conversation et prépare une réponse…');
        try {
            const res = await fetch(@json(url('/admin/support')) + '/' + id + '/ia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ mode, draft: el.value, instruction: instruction.value })
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.reply) {
                status(data.error || (res.status === 429 ? 'Trop de demandes à l\'IA : attends une minute.' : 'L\'IA n\'a pas pu répondre. Réessaie dans un instant.'), 'err');
                return;
            }
            previous = el.value;
            el.value = data.reply;
            el.dispatchEvent(new Event('input'));
            el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 220) + 'px';
            el.focus();
            status('Réponse proposée par l\'IA : relis-la et corrige-la avant d\'envoyer.', 'ok', previous.trim() !== '');
        } catch (e) {
            status('Connexion perdue : réessaie.', 'err');
        } finally {
            buttons.forEach(b => b.disabled = false);
        }
    }

    bar.addEventListener('click', e => { const b = e.target.closest('[data-ai]'); if (b) run(b.dataset.ai); });
    instruction.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); run(input()?.value.trim() ? 'ameliorer' : 'rediger'); } });
})();
</script>
