{{-- Partial réutilisable : liste des pays en <option>
     Usage : @include('partials.country-select-options', ['selected' => $user->country])
--}}
<option value="">— Choisir un pays —</option>

<optgroup label="🌍 Afrique de l'Ouest">
    <option value="GN"  data-flag="🇬🇳" data-devise="GNF" @selected(($selected??'')==='GN')>Guinée</option>
    <option value="SN"  data-flag="🇸🇳" data-devise="XOF" @selected(($selected??'')==='SN')>Sénégal</option>
    <option value="CI"  data-flag="🇨🇮" data-devise="XOF" @selected(($selected??'')==='CI')>Côte d'Ivoire</option>
    <option value="ML"  data-flag="🇲🇱" data-devise="XOF" @selected(($selected??'')==='ML')>Mali</option>
    <option value="BF"  data-flag="🇧🇫" data-devise="XOF" @selected(($selected??'')==='BF')>Burkina Faso</option>
    <option value="NE"  data-flag="🇳🇪" data-devise="XOF" @selected(($selected??'')==='NE')>Niger</option>
    <option value="TG"  data-flag="🇹🇬" data-devise="XOF" @selected(($selected??'')==='TG')>Togo</option>
    <option value="BJ"  data-flag="🇧🇯" data-devise="XOF" @selected(($selected??'')==='BJ')>Bénin</option>
    <option value="GW"  data-flag="🇬🇼" data-devise="XOF" @selected(($selected??'')==='GW')>Guinée-Bissau</option>
    <option value="MR"  data-flag="🇲🇷" data-devise="MRU" @selected(($selected??'')==='MR')>Mauritanie</option>
    <option value="GM"  data-flag="🇬🇲" data-devise="GMD" @selected(($selected??'')==='GM')>Gambie</option>
    <option value="SL"  data-flag="🇸🇱" data-devise="SLE" @selected(($selected??'')==='SL')>Sierra Leone</option>
    <option value="LR"  data-flag="🇱🇷" data-devise="LRD" @selected(($selected??'')==='LR')>Libéria</option>
    <option value="CV"  data-flag="🇨🇻" data-devise="CVE" @selected(($selected??'')==='CV')>Cap-Vert</option>
    <option value="NG"  data-flag="🇳🇬" data-devise="NGN" @selected(($selected??'')==='NG')>Nigeria</option>
    <option value="GH"  data-flag="🇬🇭" data-devise="GHS" @selected(($selected??'')==='GH')>Ghana</option>
</optgroup>

<optgroup label="🌍 Afrique Centrale">
    <option value="CM"  data-flag="🇨🇲" data-devise="XAF" @selected(($selected??'')==='CM')>Cameroun</option>
    <option value="CG"  data-flag="🇨🇬" data-devise="XAF" @selected(($selected??'')==='CG')>Congo</option>
    <option value="CD"  data-flag="🇨🇩" data-devise="CDF" @selected(($selected??'')==='CD')>RD Congo</option>
    <option value="GA"  data-flag="🇬🇦" data-devise="XAF" @selected(($selected??'')==='GA')>Gabon</option>
    <option value="TD"  data-flag="🇹🇩" data-devise="XAF" @selected(($selected??'')==='TD')>Tchad</option>
    <option value="CF"  data-flag="🇨🇫" data-devise="XAF" @selected(($selected??'')==='CF')>Centrafrique</option>
    <option value="GQ"  data-flag="🇬🇶" data-devise="XAF" @selected(($selected??'')==='GQ')>Guinée Équatoriale</option>
    <option value="AO"  data-flag="🇦🇴" data-devise="AOA" @selected(($selected??'')==='AO')>Angola</option>
</optgroup>

<optgroup label="🌍 Afrique du Nord">
    <option value="MA"  data-flag="🇲🇦" data-devise="MAD" @selected(($selected??'')==='MA')>Maroc</option>
    <option value="DZ"  data-flag="🇩🇿" data-devise="DZD" @selected(($selected??'')==='DZ')>Algérie</option>
    <option value="TN"  data-flag="🇹🇳" data-devise="TND" @selected(($selected??'')==='TN')>Tunisie</option>
    <option value="LY"  data-flag="🇱🇾" data-devise="LYD" @selected(($selected??'')==='LY')>Libye</option>
    <option value="EG"  data-flag="🇪🇬" data-devise="EGP" @selected(($selected??'')==='EG')>Égypte</option>
</optgroup>

<optgroup label="🌍 Afrique de l'Est & Australe">
    <option value="ET"  data-flag="🇪🇹" data-devise="ETB" @selected(($selected??'')==='ET')>Éthiopie</option>
    <option value="KE"  data-flag="🇰🇪" data-devise="KES" @selected(($selected??'')==='KE')>Kenya</option>
    <option value="TZ"  data-flag="🇹🇿" data-devise="TZS" @selected(($selected??'')==='TZ')>Tanzanie</option>
    <option value="UG"  data-flag="🇺🇬" data-devise="UGX" @selected(($selected??'')==='UG')>Ouganda</option>
    <option value="MG"  data-flag="🇲🇬" data-devise="MGA" @selected(($selected??'')==='MG')>Madagascar</option>
    <option value="MU"  data-flag="🇲🇺" data-devise="MUR" @selected(($selected??'')==='MU')>Maurice</option>
    <option value="ZA"  data-flag="🇿🇦" data-devise="ZAR" @selected(($selected??'')==='ZA')>Afrique du Sud</option>
    <option value="MZ"  data-flag="🇲🇿" data-devise="MZN" @selected(($selected??'')==='MZ')>Mozambique</option>
</optgroup>

<optgroup label="🌍 Europe">
    <option value="FR"  data-flag="🇫🇷" data-devise="EUR" @selected(($selected??'')==='FR')>France</option>
    <option value="BE"  data-flag="🇧🇪" data-devise="EUR" @selected(($selected??'')==='BE')>Belgique</option>
    <option value="CH"  data-flag="🇨🇭" data-devise="CHF" @selected(($selected??'')==='CH')>Suisse</option>
    <option value="LU"  data-flag="🇱🇺" data-devise="EUR" @selected(($selected??'')==='LU')>Luxembourg</option>
    <option value="DE"  data-flag="🇩🇪" data-devise="EUR" @selected(($selected??'')==='DE')>Allemagne</option>
    <option value="ES"  data-flag="🇪🇸" data-devise="EUR" @selected(($selected??'')==='ES')>Espagne</option>
    <option value="IT"  data-flag="🇮🇹" data-devise="EUR" @selected(($selected??'')==='IT')>Italie</option>
    <option value="PT"  data-flag="🇵🇹" data-devise="EUR" @selected(($selected??'')==='PT')>Portugal</option>
    <option value="NL"  data-flag="🇳🇱" data-devise="EUR" @selected(($selected??'')==='NL')>Pays-Bas</option>
    <option value="GB"  data-flag="🇬🇧" data-devise="GBP" @selected(($selected??'')==='GB')>Royaume-Uni</option>
    <option value="SE"  data-flag="🇸🇪" data-devise="SEK" @selected(($selected??'')==='SE')>Suède</option>
    <option value="NO"  data-flag="🇳🇴" data-devise="NOK" @selected(($selected??'')==='NO')>Norvège</option>
    <option value="DK"  data-flag="🇩🇰" data-devise="DKK" @selected(($selected??'')==='DK')>Danemark</option>
    <option value="PL"  data-flag="🇵🇱" data-devise="PLN" @selected(($selected??'')==='PL')>Pologne</option>
    <option value="RO"  data-flag="🇷🇴" data-devise="RON" @selected(($selected??'')==='RO')>Roumanie</option>
    <option value="RU"  data-flag="🇷🇺" data-devise="RUB" @selected(($selected??'')==='RU')>Russie</option>
    <option value="TR"  data-flag="🇹🇷" data-devise="TRY" @selected(($selected??'')==='TR')>Turquie</option>
</optgroup>

<optgroup label="🌍 Amériques">
    <option value="US"  data-flag="🇺🇸" data-devise="USD" @selected(($selected??'')==='US')>États-Unis</option>
    <option value="CA"  data-flag="🇨🇦" data-devise="CAD" @selected(($selected??'')==='CA')>Canada</option>
    <option value="MX"  data-flag="🇲🇽" data-devise="MXN" @selected(($selected??'')==='MX')>Mexique</option>
    <option value="BR"  data-flag="🇧🇷" data-devise="BRL" @selected(($selected??'')==='BR')>Brésil</option>
    <option value="AR"  data-flag="🇦🇷" data-devise="ARS" @selected(($selected??'')==='AR')>Argentine</option>
    <option value="CO"  data-flag="🇨🇴" data-devise="COP" @selected(($selected??'')==='CO')>Colombie</option>
</optgroup>

<optgroup label="🌍 Moyen-Orient">
    <option value="SA"  data-flag="🇸🇦" data-devise="SAR" @selected(($selected??'')==='SA')>Arabie Saoudite</option>
    <option value="AE"  data-flag="🇦🇪" data-devise="AED" @selected(($selected??'')==='AE')>Émirats Arabes Unis</option>
    <option value="QA"  data-flag="🇶🇦" data-devise="QAR" @selected(($selected??'')==='QA')>Qatar</option>
    <option value="KW"  data-flag="🇰🇼" data-devise="KWD" @selected(($selected??'')==='KW')>Koweït</option>
    <option value="IL"  data-flag="🇮🇱" data-devise="ILS" @selected(($selected??'')==='IL')>Israël</option>
    <option value="LB"  data-flag="🇱🇧" data-devise="LBP" @selected(($selected??'')==='LB')>Liban</option>
</optgroup>

<optgroup label="🌍 Asie & Pacifique">
    <option value="CN"  data-flag="🇨🇳" data-devise="CNY" @selected(($selected??'')==='CN')>Chine</option>
    <option value="JP"  data-flag="🇯🇵" data-devise="JPY" @selected(($selected??'')==='JP')>Japon</option>
    <option value="KR"  data-flag="🇰🇷" data-devise="KRW" @selected(($selected??'')==='KR')>Corée du Sud</option>
    <option value="IN"  data-flag="🇮🇳" data-devise="INR" @selected(($selected??'')==='IN')>Inde</option>
    <option value="SG"  data-flag="🇸🇬" data-devise="SGD" @selected(($selected??'')==='SG')>Singapour</option>
    <option value="MY"  data-flag="🇲🇾" data-devise="MYR" @selected(($selected??'')==='MY')>Malaisie</option>
    <option value="ID"  data-flag="🇮🇩" data-devise="IDR" @selected(($selected??'')==='ID')>Indonésie</option>
    <option value="AU"  data-flag="🇦🇺" data-devise="AUD" @selected(($selected??'')==='AU')>Australie</option>
    <option value="NZ"  data-flag="🇳🇿" data-devise="NZD" @selected(($selected??'')==='NZ')>Nouvelle-Zélande</option>
</optgroup>
