// Test de base : vérifie que l'app démarre sans planter (écran de démarrage visible).

import 'package:flutter_test/flutter_test.dart';

import 'package:shopio_client/main.dart';

void main() {
  testWidgets('App démarre et affiche l\'écran de chargement', (WidgetTester tester) async {
    await tester.pumpWidget(const ShopioApp());
    expect(find.text('Shopio'), findsOneWidget);
  });
}
