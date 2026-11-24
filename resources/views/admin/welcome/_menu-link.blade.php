{{-- Lien « Bienvenue WhatsApp » du menu admin (juste après « Annonces par email »), avec la pastille verte
     des nouveaux inscrits pas encore contactés. Mise à jour en direct par la page Bienvenue ([data-wl-badge]).
     Variable : $active (true sur la page Bienvenue WhatsApp) --}}
@php $wlPending = \App\Http\Controllers\Admin\WelcomeController::pending(); @endphp
<a href="{{ route('admin.welcome.index') }}" class="sb-a{{ !empty($active) ? ' on' : '' }}"@if(!empty($active)) aria-current="page"@endif>
    <span class="sb-i"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg></span><span>Bienvenue WhatsApp</span>
    <span data-wl-badge title="Nouveaux inscrits à contacter" style="margin-left:auto;display:{{ $wlPending > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#16A34A;color:#fff;font-size:11px;font-weight:800;line-height:1">{{ $wlPending }}</span>
</a>
