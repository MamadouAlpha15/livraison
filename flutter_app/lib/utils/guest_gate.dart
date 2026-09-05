import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../screens/auth/login_screen.dart';

/// Ouvre une URL dans le navigateur externe, avec un message d'erreur visible
/// en cas d'échec (au lieu de ne rien faire silencieusement).
Future<void> openExternalUrl(BuildContext context, String url) async {
  try {
    final ok = await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    if (!ok && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Impossible d'ouvrir le navigateur.")));
    }
  } catch (_) {
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Impossible d'ouvrir le navigateur.")));
    }
  }
}

/// Affiche une invite de connexion quand une action nécessite un compte
/// (favoris, panier, messagerie, commandes...) — utilisé quand l'app est
/// parcourue sans compte (voir AuthProvider.guestMode), exactement comme
/// le site où ces sections sont derrière `auth + role:client`.
Future<void> requireLogin(BuildContext context, {String message = 'Connectez-vous pour accéder à cette fonctionnalité.'}) async {
  await showDialog(
    context: context,
    builder: (ctx) => AlertDialog(
      title: const Text('Compte requis'),
      content: Text(message),
      actions: [
        TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Annuler')),
        FilledButton(
          onPressed: () {
            Navigator.of(ctx).pop();
            Navigator.of(context).push(MaterialPageRoute(builder: (_) => const LoginScreen()));
          },
          child: const Text('Se connecter'),
        ),
      ],
    ),
  );
}

/// Emballe un écran qui nécessite un compte : affiche un message + bouton de
/// connexion tant que l'utilisateur n'est pas connecté (mode invité).
class RequireAuthScreen extends StatelessWidget {
  final bool loggedIn;
  final String title;
  final IconData icon;
  final String message;
  final Widget child;
  const RequireAuthScreen({
    super.key,
    required this.loggedIn,
    required this.title,
    required this.icon,
    required this.message,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    if (loggedIn) return child;
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 56, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(message, textAlign: TextAlign.center, style: const TextStyle(color: Colors.grey)),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const LoginScreen())),
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1)),
                child: const Text('Se connecter'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
