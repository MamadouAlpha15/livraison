import 'package:flutter/material.dart';

/// Clé de navigation globale — permet de naviguer depuis un service (ex: au
/// clic sur une notification push) sans avoir de BuildContext d'écran sous la main.
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
