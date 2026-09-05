/// Même liste que resources/views/auth/register.blade.php côté site (liste
/// complète — celle de GoogleController::countries() côté serveur est plus
/// courte et ne sert que de repli pour le libellé sur les autres écrans).
const Map<String, List<String>> kCountries = {
  'BJ': ['🇧🇯', 'Bénin'], 'BF': ['🇧🇫', 'Burkina Faso'], 'CV': ['🇨🇻', 'Cap-Vert'],
  'CI': ['🇨🇮', "Côte d'Ivoire"], 'GM': ['🇬🇲', 'Gambie'], 'GH': ['🇬🇭', 'Ghana'],
  'GN': ['🇬🇳', 'Guinée'], 'GW': ['🇬🇼', 'Guinée-Bissau'], 'GQ': ['🇬🇶', 'Guinée équatoriale'],
  'LR': ['🇱🇷', 'Libéria'], 'ML': ['🇲🇱', 'Mali'], 'MR': ['🇲🇷', 'Mauritanie'],
  'NE': ['🇳🇪', 'Niger'], 'NG': ['🇳🇬', 'Nigeria'], 'SN': ['🇸🇳', 'Sénégal'],
  'SL': ['🇸🇱', 'Sierra Leone'], 'TG': ['🇹🇬', 'Togo'],
  'AO': ['🇦🇴', 'Angola'], 'CM': ['🇨🇲', 'Cameroun'], 'CF': ['🇨🇫', 'Centrafrique'],
  'TD': ['🇹🇩', 'Tchad'], 'CG': ['🇨🇬', 'Congo'], 'CD': ['🇨🇩', 'RD Congo'],
  'GA': ['🇬🇦', 'Gabon'], 'ST': ['🇸🇹', 'São Tomé-et-Príncipe'],
  'BI': ['🇧🇮', 'Burundi'], 'KM': ['🇰🇲', 'Comores'], 'DJ': ['🇩🇯', 'Djibouti'],
  'ER': ['🇪🇷', 'Érythrée'], 'ET': ['🇪🇹', 'Éthiopie'], 'KE': ['🇰🇪', 'Kenya'],
  'MG': ['🇲🇬', 'Madagascar'], 'MW': ['🇲🇼', 'Malawi'], 'MU': ['🇲🇺', 'Maurice'],
  'MZ': ['🇲🇿', 'Mozambique'], 'RW': ['🇷🇼', 'Rwanda'], 'SC': ['🇸🇨', 'Seychelles'],
  'SO': ['🇸🇴', 'Somalie'], 'SS': ['🇸🇸', 'Soudan du Sud'], 'SD': ['🇸🇩', 'Soudan'],
  'TZ': ['🇹🇿', 'Tanzanie'], 'UG': ['🇺🇬', 'Ouganda'], 'ZM': ['🇿🇲', 'Zambie'], 'ZW': ['🇿🇼', 'Zimbabwe'],
  'BW': ['🇧🇼', 'Botswana'], 'LS': ['🇱🇸', 'Lesotho'], 'NA': ['🇳🇦', 'Namibie'],
  'ZA': ['🇿🇦', 'Afrique du Sud'], 'SZ': ['🇸🇿', 'Eswatini'],
  'DZ': ['🇩🇿', 'Algérie'], 'EG': ['🇪🇬', 'Égypte'], 'LY': ['🇱🇾', 'Libye'],
  'MA': ['🇲🇦', 'Maroc'], 'TN': ['🇹🇳', 'Tunisie'],
  'AL': ['🇦🇱', 'Albanie'], 'DE': ['🇩🇪', 'Allemagne'], 'AT': ['🇦🇹', 'Autriche'],
  'BE': ['🇧🇪', 'Belgique'], 'FR': ['🇫🇷', 'France'], 'GB': ['🇬🇧', 'Royaume-Uni'],
  'IT': ['🇮🇹', 'Italie'], 'ES': ['🇪🇸', 'Espagne'], 'PT': ['🇵🇹', 'Portugal'],
  'NL': ['🇳🇱', 'Pays-Bas'], 'CH': ['🇨🇭', 'Suisse'],
  'CA': ['🇨🇦', 'Canada'], 'US': ['🇺🇸', 'États-Unis'], 'BR': ['🇧🇷', 'Brésil'],
  'AR': ['🇦🇷', 'Argentine'], 'MX': ['🇲🇽', 'Mexique'],
  'CN': ['🇨🇳', 'Chine'], 'JP': ['🇯🇵', 'Japon'], 'IN': ['🇮🇳', 'Inde'],
  'AU': ['🇦🇺', 'Australie'], 'NZ': ['🇳🇿', 'Nouvelle-Zélande'],
};

String countryLabel(String code) {
  final c = kCountries[code];
  return c != null ? '${c[0]} ${c[1]}' : code;
}
