/// Formate un prix comme sur le site : "1 500 000" (espace entre les milliers).
/// Écrit à la main (pas via intl/NumberFormat) pour éviter d'avoir à
/// initialiser les données de locale au démarrage de l'app.
String formatPrice(num value) {
  final digits = value.round().toString();
  final buffer = StringBuffer();
  for (int i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) buffer.write(' ');
    buffer.write(digits[i]);
  }
  return buffer.toString();
}

String _two(int n) => n.toString().padLeft(2, '0');

String formatDateTime(DateTime dt) {
  final d = dt.toLocal();
  return '${_two(d.day)}/${_two(d.month)}/${d.year} à ${_two(d.hour)}:${_two(d.minute)}';
}
