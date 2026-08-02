<x-guest-layout>

<style>
.otp-inputs { display:flex; gap:8px; justify-content:center; margin:6px 0 4px; }
.otp-box {
    width:46px; height:54px; text-align:center; font-size:22px; font-weight:700;
    border:1.5px solid #e5e7eb; border-radius:10px; color:#111827; outline:none;
    transition:border-color .15s, box-shadow .15s; font-family:inherit;
}
.otp-box:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.13); }
.otp-box.f-input-err { border-color:#ef4444; }
@media(max-width:480px){ .otp-box{ width:40px; height:48px; font-size:19px; } }
.resend-row { text-align:center; margin-top:18px; font-size:13px; color:#6b7280; }
.resend-btn { background:none; border:none; color:#6366f1; font-weight:600; font-size:13px; cursor:pointer; padding:0; font-family:inherit; }
.resend-btn:hover { text-decoration:underline; }
.resend-btn:disabled { color:#9ca3af; cursor:not-allowed; text-decoration:none; }
</style>

<div class="ab-title">Vérifiez votre email 📩</div>
<p class="ab-sub">
    Un code à 6 chiffres a été envoyé à<br>
    <strong style="color:#374151">{{ $email }}</strong>
</p>

@if (session('status'))
<div class="f-alert f-alert-success">{{ session('status') }}</div>
@endif

@if ($errors->any())
<div class="f-alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('verification.otp.verify') }}" id="otpForm">
    @csrf
    <input type="hidden" name="code" id="codeHidden">

    <div class="otp-inputs">
        @for ($i = 0; $i < 6; $i++)
        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-idx="{{ $i }}">
        @endfor
    </div>

    <button type="submit" class="btn-auth" id="otpSubmitBtn" style="margin-top:22px">Vérifier le code</button>
</form>

<div class="resend-row">
    Vous n'avez rien reçu ?
    <form method="POST" action="{{ route('verification.otp.resend') }}" style="display:inline" id="resendForm">
        @csrf
        <button type="submit" class="resend-btn" id="resendBtn">Renvoyer le code</button>
    </form>
</div>

<script>
(function () {
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
    var hidden = document.getElementById('codeHidden');

    function sync() {
        hidden.value = boxes.map(function (b) { return b.value; }).join('');
    }

    boxes.forEach(function (box, i) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            box.classList.remove('f-input-err');
            if (box.value && boxes[i + 1]) boxes[i + 1].focus();
            sync();
        });
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && boxes[i - 1]) boxes[i - 1].focus();
        });
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6).split('');
            digits.forEach(function (d, idx) { if (boxes[idx]) boxes[idx].value = d; });
            sync();
            var last = boxes[Math.min(digits.length, 5)];
            if (last) last.focus();
        });
    });

    if (boxes[0]) boxes[0].focus();

    document.getElementById('otpForm').addEventListener('submit', function (e) {
        sync();
        if (hidden.value.length !== 6) {
            e.preventDefault();
            boxes.forEach(function (b) { b.classList.add('f-input-err'); });
            return;
        }
        var btn = document.getElementById('otpSubmitBtn');
        if (btn.disabled) { e.preventDefault(); return; }
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-spinner"></span> Vérification…';
    });

    var resendBtn = document.getElementById('resendBtn');
    document.getElementById('resendForm').addEventListener('submit', function () {
        resendBtn.disabled = true;
        resendBtn.textContent = 'Envoi en cours…';
        var seconds = 30;
        var interval = setInterval(function () {
            seconds--;
            resendBtn.textContent = 'Renvoyer (' + seconds + 's)';
            if (seconds <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Renvoyer le code';
            }
        }, 1000);
    });
})();
</script>

</x-guest-layout>
