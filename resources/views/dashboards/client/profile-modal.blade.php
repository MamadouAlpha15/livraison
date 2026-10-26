<div id="profileOverlay" onclick="if(event.target===this)closeProfileModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:700;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:500px;max-height:90vh;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.3);animation:slideUp .28s cubic-bezier(.23,1,.32,1);display:flex;flex-direction:column">

        {{-- ── HEADER ── --}}
        <div style="background:linear-gradient(135deg,#1668D8 0%,#1260C9 100%);padding:24px 24px 20px;flex-shrink:0;position:relative;overflow:hidden">
            {{-- Cercles déco --}}
            <div style="position:absolute;right:-30px;top:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none"></div>
            <div style="position:absolute;right:50px;bottom:-40px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.06);pointer-events:none"></div>

            <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1">
                {{-- Avatar --}}
                <div style="position:relative;flex-shrink:0">
                    <div style="width:58px;height:58px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;color:#fff;font-family:var(--display)">
                        {{ $initials }}
                    </div>
                    @if($countryFlag)
                    <span style="position:absolute;bottom:-2px;right:-4px;font-size:18px;line-height:1;background:#fff;border-radius:50%;padding:2px;box-shadow:0 0 0 2px rgba(31,124,246,.4)">{{ $countryFlag }}</span>
                    @endif
                </div>
                {{-- Infos --}}
                <div style="flex:1;min-width:0">
                    <div style="font-size:17px;font-weight:900;color:#fff;font-family:var(--display);letter-spacing:-.3px">{{ $user->name }}</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->email }}</div>
                    @if($countryName)
                    <div style="display:inline-flex;align-items:center;gap:4px;margin-top:5px;background:rgba(255,255,255,.18);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;color:#fff">
                        {{ $countryFlag }} {{ $countryName }}
                    </div>
                    @endif
                </div>
                {{-- Fermer --}}
                <button onclick="closeProfileModal()"
                        style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.3);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.18)'">✕</button>
            </div>

            {{-- Onglets --}}
            <div style="display:flex;gap:6px;margin-top:18px;position:relative;z-index:1">
                <button id="ptab-info" onclick="switchProfileTab('info')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.25);color:#fff">
                    {!! \App\Support\IconLibrary::svg('user', '', 15) !!} Profil
                </button>
                <button id="ptab-pwd" onclick="switchProfileTab('pwd')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)">
                    {!! \App\Support\IconLibrary::svg('lock', '', 15) !!} Mot de passe
                </button>
                <button id="ptab-del" onclick="switchProfileTab('del')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)">
                    {!! \App\Support\IconLibrary::svg('trash', '', 15) !!} Supprimer
                </button>
            </div>
        </div>

        {{-- ── CORPS SCROLLABLE ── --}}
        <div style="overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:#E3E8F2 transparent">

            {{-- ════ ONGLET 1 : INFORMATIONS ════ --}}
            <div id="ptab-info-body">
                <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                    @csrf @method('PATCH')
                    <div style="padding:22px 24px;display:flex;flex-direction:column;gap:14px">

                        {{-- Flash erreurs profil --}}
                        @if($errors->any() && !$errors->updatePassword->any() && !$errors->userDeletion->any())
                        <div style="background:#fff7ed;border:1.5px solid #f06a0f;border-radius:10px;padding:10px 14px;font-size:12.5px;color:#92400e;display:flex;gap:8px;align-items:flex-start">
                            <span style="display:inline-flex">{!! \App\Support\IconLibrary::svg('alert', '', 16) !!}</span>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                        </div>
                        @endif
                        @if(session('status') === 'profile-updated')
                        <div style="background:#f0fdf4;border:1.5px solid #6ee7b7;border-radius:10px;padding:10px 14px;font-size:12.5px;color:#065f46;display:flex;gap:8px;align-items:center">
                            ✓ Profil mis à jour avec succès !
                        </div>
                        @endif

                        @php
                        $iStyle = "width:100%;padding:10px 14px 10px 40px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box";
                        $lStyle = "display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px";
                        @endphp

                        {{-- Nom --}}
                        <div>
                            <label style="{{ $lStyle }}">Nom complet</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('user', '', 15) !!}</span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label style="{{ $lStyle }}">Adresse email</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('mail', '', 15) !!}</span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                            @if($user->email_verified_at)
                            <div style="font-size:11px;color:#16a34a;margin-top:5px">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} Email vérifié</div>
                            @endif
                        </div>

                        {{-- Téléphone --}}
                        <div>
                            <label style="{{ $lStyle }}">Téléphone</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('smartphone', '', 15) !!}</span>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+224 6XX XXX XXX" style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Adresse --}}
                        <div>
                            <label style="{{ $lStyle }}">Adresse de livraison</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('map-pin', '', 15) !!}</span>
                                <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Quartier, rue…" style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Pays --}}
                        <div>
                            <label style="{{ $lStyle }}">
                                {!! \App\Support\IconLibrary::svg('globe', '', 15) !!} Pays
                                <span style="font-size:10px;font-weight:500;color:var(--orange);text-transform:none;letter-spacing:0"> — changer de pays actualise vos boutiques</span>
                            </label>
                            <div style="position:relative">
                                <span id="modalFlagPreview" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:20px;pointer-events:none;z-index:1;line-height:1">@if($countryFlag){{ $countryFlag }}@else{!! \App\Support\IconLibrary::svg('globe', '', 18) !!}@endif</span>
                                <select name="country" id="modalCountry" onchange="updateModalFlag(this)"
                                        style="width:100%;padding:10px 14px 10px 44px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;appearance:none;-webkit-appearance:none;cursor:pointer;outline:none;box-sizing:border-box"
                                        onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                        onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                    <option value="">-- Sélectionner un pays --</option>
                                    @php
                                    $allCountries = [
                                        'Afrique de l\'Ouest' => [
                                            'BJ'=>'🇧🇯 Bénin','BF'=>'🇧🇫 Burkina Faso','CV'=>'🇨🇻 Cap-Vert',
                                            'CI'=>"🇨🇮 Côte d'Ivoire",'GM'=>'🇬🇲 Gambie','GH'=>'🇬🇭 Ghana',
                                            'GN'=>'🇬🇳 Guinée','GW'=>'🇬🇼 Guinée-Bissau','LR'=>'🇱🇷 Libéria',
                                            'ML'=>'🇲🇱 Mali','MR'=>'🇲🇷 Mauritanie','NE'=>'🇳🇪 Niger',
                                            'NG'=>'🇳🇬 Nigéria','SN'=>'🇸🇳 Sénégal','SL'=>'🇸🇱 Sierra Leone',
                                            'TG'=>'🇹🇬 Togo',
                                        ],
                                        'Afrique Centrale' => [
                                            'CM'=>'🇨🇲 Cameroun','CF'=>'🇨🇫 Centrafrique','TD'=>'🇹🇩 Tchad',
                                            'CG'=>'🇨🇬 Congo','CD'=>'🇨🇩 RD Congo','GQ'=>'🇬🇶 Guinée Équatoriale',
                                            'GA'=>'🇬🇦 Gabon','ST'=>'🇸🇹 São Tomé','BI'=>'🇧🇮 Burundi',
                                            'RW'=>'🇷🇼 Rwanda',
                                        ],
                                        'Afrique de l\'Est' => [
                                            'DJ'=>'🇩🇯 Djibouti','ER'=>'🇪🇷 Érythrée','ET'=>'🇪🇹 Éthiopie',
                                            'KE'=>'🇰🇪 Kenya','KM'=>'🇰🇲 Comores','MG'=>'🇲🇬 Madagascar',
                                            'MW'=>'🇲🇼 Malawi','MU'=>'🇲🇺 Maurice','MZ'=>'🇲🇿 Mozambique',
                                            'SC'=>'🇸🇨 Seychelles','SO'=>'🇸🇴 Somalie','SS'=>'🇸🇸 Soudan du Sud',
                                            'SD'=>'🇸🇩 Soudan','TZ'=>'🇹🇿 Tanzanie','UG'=>'🇺🇬 Ouganda',
                                            'ZM'=>'🇿🇲 Zambie','ZW'=>'🇿🇼 Zimbabwe',
                                        ],
                                        'Afrique du Nord' => [
                                            'DZ'=>'🇩🇿 Algérie','EG'=>'🇪🇬 Égypte','LY'=>'🇱🇾 Libye',
                                            'MA'=>'🇲🇦 Maroc','SD'=>'🇸🇩 Soudan','TN'=>'🇹🇳 Tunisie',
                                        ],
                                        'Afrique Australe' => [
                                            'AO'=>'🇦🇴 Angola','BW'=>'🇧🇼 Botswana','LS'=>'🇱🇸 Lesotho',
                                            'NA'=>'🇳🇦 Namibie','ZA'=>'🇿🇦 Afrique du Sud','SZ'=>'🇸🇿 Eswatini',
                                        ],
                                        'Europe' => [
                                            'AL'=>'🇦🇱 Albanie','DE'=>'🇩🇪 Allemagne','AT'=>'🇦🇹 Autriche',
                                            'BE'=>'🇧🇪 Belgique','BA'=>'🇧🇦 Bosnie','BG'=>'🇧🇬 Bulgarie',
                                            'HR'=>'🇭🇷 Croatie','CY'=>'🇨🇾 Chypre','DK'=>'🇩🇰 Danemark',
                                            'ES'=>'🇪🇸 Espagne','EE'=>'🇪🇪 Estonie','FI'=>'🇫🇮 Finlande',
                                            'FR'=>'🇫🇷 France','GR'=>'🇬🇷 Grèce','HU'=>'🇭🇺 Hongrie',
                                            'IE'=>'🇮🇪 Irlande','IS'=>'🇮🇸 Islande','IT'=>'🇮🇹 Italie',
                                            'XK'=>'🇽🇰 Kosovo','LV'=>'🇱🇻 Lettonie','LI'=>'🇱🇮 Liechtenstein',
                                            'LT'=>'🇱🇹 Lituanie','LU'=>'🇱🇺 Luxembourg','MK'=>'🇲🇰 Macédoine',
                                            'MT'=>'🇲🇹 Malte','MD'=>'🇲🇩 Moldavie','MC'=>'🇲🇨 Monaco',
                                            'ME'=>'🇲🇪 Monténégro','NO'=>'🇳🇴 Norvège','NL'=>'🇳🇱 Pays-Bas',
                                            'PL'=>'🇵🇱 Pologne','PT'=>'🇵🇹 Portugal','CZ'=>'🇨🇿 Rép. Tchèque',
                                            'RO'=>'🇷🇴 Roumanie','GB'=>'🇬🇧 Royaume-Uni','RU'=>'🇷🇺 Russie',
                                            'RS'=>'🇷🇸 Serbie','SK'=>'🇸🇰 Slovaquie','SI'=>'🇸🇮 Slovénie',
                                            'SE'=>'🇸🇪 Suède','CH'=>'🇨🇭 Suisse','UA'=>'🇺🇦 Ukraine',
                                        ],
                                        'Amériques' => [
                                            'AR'=>'🇦🇷 Argentine','BB'=>'🇧🇧 Barbade','BO'=>'🇧🇴 Bolivie',
                                            'BR'=>'🇧🇷 Brésil','CA'=>'🇨🇦 Canada','CL'=>'🇨🇱 Chili',
                                            'CO'=>'🇨🇴 Colombie','CR'=>'🇨🇷 Costa Rica','CU'=>'🇨🇺 Cuba',
                                            'DM'=>'🇩🇲 Dominique','DO'=>'🇩🇴 Rép. Dominicaine','EC'=>'🇪🇨 Équateur',
                                            'SV'=>'🇸🇻 Salvador','US'=>'🇺🇸 États-Unis','GT'=>'🇬🇹 Guatemala',
                                            'GY'=>'🇬🇾 Guyana','HT'=>'🇭🇹 Haïti','HN'=>'🇭🇳 Honduras',
                                            'JM'=>'🇯🇲 Jamaïque','MX'=>'🇲🇽 Mexique','NI'=>'🇳🇮 Nicaragua',
                                            'PA'=>'🇵🇦 Panama','PY'=>'🇵🇾 Paraguay','PE'=>'🇵🇪 Pérou',
                                            'TT'=>'🇹🇹 Trinité-et-Tobago','UY'=>'🇺🇾 Uruguay','VE'=>'🇻🇪 Venezuela',
                                        ],
                                        'Asie' => [
                                            'AF'=>'🇦🇫 Afghanistan','AM'=>'🇦🇲 Arménie','AZ'=>'🇦🇿 Azerbaïdjan',
                                            'BH'=>'🇧🇭 Bahreïn','BD'=>'🇧🇩 Bangladesh','BT'=>'🇧🇹 Bhoutan',
                                            'MM'=>'🇲🇲 Birmanie','BN'=>'🇧🇳 Brunei','KH'=>'🇰🇭 Cambodge',
                                            'CN'=>'🇨🇳 Chine','KP'=>'🇰🇵 Corée du Nord','KR'=>'🇰🇷 Corée du Sud',
                                            'AE'=>'🇦🇪 Émirats Arabes','GE'=>'🇬🇪 Géorgie','IN'=>'🇮🇳 Inde',
                                            'ID'=>'🇮🇩 Indonésie','IR'=>'🇮🇷 Iran','IQ'=>'🇮🇶 Irak',
                                            'IL'=>'🇮🇱 Israël','JP'=>'🇯🇵 Japon','JO'=>'🇯🇴 Jordanie',
                                            'KZ'=>'🇰🇿 Kazakhstan','KW'=>'🇰🇼 Koweït','KG'=>'🇰🇬 Kirghizistan',
                                            'LA'=>'🇱🇦 Laos','LB'=>'🇱🇧 Liban','MY'=>'🇲🇾 Malaisie',
                                            'MV'=>'🇲🇻 Maldives','MN'=>'🇲🇳 Mongolie','NP'=>'🇳🇵 Népal',
                                            'OM'=>'🇴🇲 Oman','UZ'=>'🇺🇿 Ouzbékistan','PK'=>'🇵🇰 Pakistan',
                                            'PS'=>'🇵🇸 Palestine','PH'=>'🇵🇭 Philippines','QA'=>'🇶🇦 Qatar',
                                            'SA'=>'🇸🇦 Arabie Saoudite','SG'=>'🇸🇬 Singapour','LK'=>'🇱🇰 Sri Lanka',
                                            'SY'=>'🇸🇾 Syrie','TJ'=>'🇹🇯 Tadjikistan','TW'=>'🇹🇼 Taïwan',
                                            'TH'=>'🇹🇭 Thaïlande','TL'=>'🇹🇱 Timor-Leste','TM'=>'🇹🇲 Turkménistan',
                                            'TR'=>'🇹🇷 Turquie','VN'=>'🇻🇳 Viêt Nam','YE'=>'🇾🇪 Yémen',
                                        ],
                                        'Océanie' => [
                                            'AU'=>'🇦🇺 Australie','FJ'=>'🇫🇯 Fidji','KI'=>'🇰🇮 Kiribati',
                                            'MH'=>'🇲🇭 Îles Marshall','FM'=>'🇫🇲 Micronésie','NR'=>'🇳🇷 Nauru',
                                            'NZ'=>'🇳🇿 Nouvelle-Zélande','PW'=>'🇵🇼 Palaos','PG'=>'🇵🇬 Papouasie',
                                            'WS'=>'🇼🇸 Samoa','SB'=>'🇸🇧 Salomon','TO'=>'🇹🇴 Tonga',
                                            'TV'=>'🇹🇻 Tuvalu','VU'=>'🇻🇺 Vanuatu',
                                        ],
                                    ];
                                    @endphp
                                    @foreach($allCountries as $region => $pays)
                                        <optgroup label="{{ $region }}">
                                            @foreach($pays as $code => $label)
                                            <option value="{{ $code }}" {{ $user->country === $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div style="padding:14px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font);transition:all .15s"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit"
                                style="padding:10px 26px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--orange),var(--orange-dk));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(31,124,246,.4);transition:all .15s"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(31,124,246,.5)'"
                                onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(31,124,246,.4)'">
                            {!! \App\Support\IconLibrary::svg('check', '', 16) !!} Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            {{-- ════ ONGLET 2 : MOT DE PASSE ════ --}}
            <div id="ptab-pwd-body" style="display:none">
                <form method="POST" action="{{ route('password.update') }}" id="pwdForm">
                    @csrf
                    @method('PUT')

                    <div style="padding:22px 24px 0">
                        {{-- Info --}}
                        <div style="background:linear-gradient(135deg,#fff7ed,#fff3e0);border:1.5px solid #fed7aa;border-radius:12px;padding:14px 16px;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start">
                            <span style="flex-shrink:0;display:inline-flex">{!! \App\Support\IconLibrary::svg('lock', '', 20) !!}</span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#92400e">Changer votre mot de passe</div>
                                <div style="font-size:12px;color:#b45309;margin-top:3px">Choisissez un mot de passe fort avec au moins 8 caractères.</div>
                            </div>
                        </div>

                        {{-- Erreurs validation --}}
                        @if($errors->updatePassword->any())
                        <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#991b1b">
                            @foreach($errors->updatePassword->all() as $e)
                                <div>{!! \App\Support\IconLibrary::svg('alert', '', 14) !!} {{ $e }}</div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Succès --}}
                        @if(session('status') === 'password-updated')
                        <div style="background:#f0fdf4;border:1.5px solid #6ee7b7;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#065f46">
                            {!! \App\Support\IconLibrary::svg('check', '', 14) !!} Mot de passe modifié avec succès !
                        </div>
                        @endif
                    </div>

                    <div style="padding:0 24px;display:flex;flex-direction:column;gap:14px">

                        {{-- Mot de passe actuel (caché pour les comptes Google) --}}
                        @if($user->google_id)
                        <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px">
                            <span style="flex-shrink:0;display:inline-flex">{!! \App\Support\IconLibrary::svg('globe', '', 20) !!}</span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#15803d">Compte connecté via Google</div>
                                <div style="font-size:12px;color:#166534;margin-top:2px">Vous pouvez définir un mot de passe directement sans saisir l'ancien. Vous pourrez toujours vous connecter avec Google aussi.</div>
                            </div>
                        </div>
                        @else
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Mot de passe actuel</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('lock', '', 15) !!}</span>
                                <input type="password" name="current_password" id="pwdCurrent" placeholder="••••••••" autocomplete="current-password"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid {{ $errors->updatePassword->has('current_password') ? '#ef4444' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdCurrent',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1;display:inline-flex" aria-label="Afficher le mot de passe">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!}</button>
                            </div>
                        </div>
                        @endif

                        {{-- Nouveau mot de passe --}}
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Nouveau mot de passe</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('lock', '', 15) !!}</span>
                                <input type="password" name="password" id="pwdNew" placeholder="Min. 8 caractères" autocomplete="new-password"
                                       oninput="evalPwdStrength(this.value)"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid {{ $errors->updatePassword->has('password') ? '#ef4444' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdNew',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1;display:inline-flex" aria-label="Afficher le mot de passe">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!}</button>
                            </div>
                            {{-- Barre de force --}}
                            <div style="margin-top:8px">
                                <div style="height:4px;background:#E3E8F2;border-radius:4px;overflow:hidden">
                                    <div id="pwdStrengthBar" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s"></div>
                                </div>
                                <div id="pwdStrengthLabel" style="font-size:11px;color:var(--muted);margin-top:4px"></div>
                            </div>
                        </div>

                        {{-- Confirmer --}}
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Confirmer le nouveau mot de passe</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('check', '', 15) !!}</span>
                                <input type="password" name="password_confirmation" id="pwdConfirm" placeholder="Répétez le nouveau mot de passe" autocomplete="new-password"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(31,124,246,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdConfirm',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1;display:inline-flex" aria-label="Afficher le mot de passe">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!}</button>
                            </div>
                        </div>

                    </div>
                    <div style="padding:16px 24px 20px;border-top:1px solid var(--border);margin-top:18px;display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font)"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit"
                                style="padding:10px 26px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--orange),var(--orange-dk));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(31,124,246,.4);transition:all .15s"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(31,124,246,.5)'"
                                onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(31,124,246,.4)'">
                            {!! \App\Support\IconLibrary::svg('lock', '', 16) !!} Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>

            {{-- ════ ONGLET 3 : SUPPRIMER ════ --}}
            <div id="ptab-del-body" style="display:none">
                <form method="POST" action="{{ route('profile.destroy') }}" id="delForm" onsubmit="return confirmDel()">
                    @csrf
                    @method('DELETE')

                    <div style="padding:22px 24px">

                        {{-- Zone danger --}}
                        <div style="background:#fff5f5;border:2px solid #fca5a5;border-radius:14px;padding:18px 20px;margin-bottom:20px">
                            <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:14px">
                                <div style="width:40px;height:40px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#B91C1C">{!! \App\Support\IconLibrary::svg('alert', '', 20) !!}</div>
                                <div>
                                    <div style="font-size:14px;font-weight:800;color:#991b1b">Suppression définitive du compte</div>
                                    <div style="font-size:12.5px;color:#b91c1c;margin-top:4px;line-height:1.55">
                                        Cette action est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées.
                                    </div>
                                </div>
                            </div>
                            <div style="border-top:1px solid #fca5a5;padding-top:12px;display:flex;flex-direction:column;gap:8px">
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Vos commandes et l'historique seront supprimés</div>
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Vos messages avec les boutiques seront supprimés</div>
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Votre accès à toutes les boutiques sera révoqué</div>
                            </div>
                        </div>

                        {{-- Erreur validation --}}
                        @if($errors->userDeletion->any())
                        <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#991b1b">
                            @foreach($errors->userDeletion->all() as $e)
                                <div>{!! \App\Support\IconLibrary::svg('alert', '', 14) !!} {{ $e }}</div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Confirmation --}}
                        <div style="display:flex;flex-direction:column;gap:14px">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#991b1b;margin-bottom:6px;text-transform:uppercase;letter-spacing:.6px">
                                    Confirmez avec votre mot de passe
                                </label>
                                <div style="position:relative">
                                    <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1;display:inline-flex;color:#62627C">{!! \App\Support\IconLibrary::svg('lock', '', 15) !!}</span>
                                    <input type="password" name="password" id="delPassword" placeholder="Votre mot de passe actuel" autocomplete="current-password"
                                           style="width:100%;padding:10px 44px 10px 40px;border:2px solid {{ $errors->userDeletion->has('password') ? '#ef4444' : '#fca5a5' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                           onfocus="this.style.borderColor='#ef4444';this.style.boxShadow='0 0 0 3px rgba(239,68,68,.15)'"
                                           onblur="this.style.borderColor='#fca5a5';this.style.boxShadow='none'">
                                    <button type="button" onclick="togglePwd('delPassword',this)"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1;display:inline-flex" aria-label="Afficher le mot de passe">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!}</button>
                                </div>
                            </div>

                            {{-- Checkbox --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:#fff5f5;border:1.5px solid #fca5a5;border-radius:9px">
                                <input type="checkbox" id="delConfirmCheck" onchange="toggleDelBtn()"
                                       style="margin-top:2px;width:16px;height:16px;accent-color:#ef4444;flex-shrink:0;cursor:pointer">
                                <span style="font-size:12.5px;font-weight:600;color:#991b1b;line-height:1.5">
                                    Je comprends que cette action est irréversible et je veux supprimer définitivement mon compte.
                                </span>
                            </label>
                        </div>

                    </div>
                    <div style="padding:14px 24px 20px;border-top:1px solid #fca5a5;display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font)"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit" id="delBtn" disabled
                                style="padding:10px 20px;border-radius:9px;border:none;background:#d1d5db;color:#fff;font-size:13px;font-weight:700;cursor:not-allowed;font-family:var(--font);display:flex;align-items:center;gap:7px;transition:all .2s">
                            {!! \App\Support\IconLibrary::svg('trash', '', 16) !!} Supprimer mon compte
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- fin corps scrollable --}}
    </div>
</div>
