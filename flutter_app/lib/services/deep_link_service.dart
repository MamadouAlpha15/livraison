import 'package:app_links/app_links.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../screens/auth/google_setup_screen.dart';

/// Écoute les retours Android App Links (https://shopio-app.com/...) — utilisé
/// pour la connexion "Continuer avec Google" (voir GoogleController::callback
/// côté serveur, qui redirige vers /api/v1/auth/google/return?... et compte
/// sur l'app pour intercepter ce lien avant même que le navigateur ne le charge).
class DeepLinkService {
  static final AppLinks _appLinks = AppLinks();
  // Écoute pour toute la durée de vie de l'app (jamais annulée) : pas besoin
  // de garder la subscription, juste de la démarrer une seule fois.
  static bool _initialized = false;

  static void init(BuildContext rootContext) {
    if (_initialized) return;
    _initialized = true;

    _appLinks.uriLinkStream.listen((uri) => _handle(rootContext, uri));
    _appLinks.getInitialLink().then((uri) {
      if (uri != null) _handle(rootContext, uri);
    });
  }

  static void _handle(BuildContext context, Uri uri) {
    if (!uri.path.contains('/auth/google/return')) return; // pas un retour Google

    final params = uri.queryParameters;
    final navigator = Navigator.of(context, rootNavigator: true);
    final auth = context.read<AuthProvider>();

    if (params['error'] != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(params['error']!)));
      return;
    }

    if (params['token'] != null) {
      auth.loginWithGoogleToken(params['token']!);
      return;
    }

    if (params['needs_setup'] == '1') {
      navigator.push(MaterialPageRoute(
        builder: (_) => GoogleSetupScreen(
          googleId: params['google_id'] ?? '',
          googleName: params['google_name'] ?? '',
          googleEmail: params['google_email'] ?? '',
        ),
      ));
    }
  }
}
