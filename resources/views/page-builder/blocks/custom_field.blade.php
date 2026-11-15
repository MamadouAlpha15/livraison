{{-- Bloc Champ personnalisé : la réponse est enregistrée avec la commande --}}
@php
    $f = $checkout->customFields()[$block['id']] ?? null;
    $fid = 'pb-cf-' . $block['id'];
@endphp
@if ($f)
    <div>
        <label class="pb-lbl" for="{{ $fid }}">{{ $f['label'] }}@if (!$f['required']) <span style="font-weight:600;color:var(--cs-muted)">(facultatif)</span>@endif</label>
        @if ($f['type'] === 'textarea')
            <textarea id="{{ $fid }}" class="pb-textarea" name="fields[{{ $block['id'] }}]" maxlength="500" placeholder="{{ $f['placeholder'] }}" @required($f['required'])></textarea>
        @elseif ($f['type'] === 'select')
            <select id="{{ $fid }}" class="pb-select" name="fields[{{ $block['id'] }}]" @required($f['required'])>
                <option value="">Choisir</option>
                @foreach ($f['options'] as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach
            </select>
        @else
            <input id="{{ $fid }}" class="pb-input" name="fields[{{ $block['id'] }}]" maxlength="200" placeholder="{{ $f['placeholder'] }}" @required($f['required'])>
        @endif
        <span class="pb-err" data-err="fields.{{ $block['id'] }}"></span>
    </div>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Champ personnalisé : ajoute au moins un choix (un par ligne) pour une liste.</p>
@endif
