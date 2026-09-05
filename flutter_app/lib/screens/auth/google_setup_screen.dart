import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../utils/countries.dart';
import '../../utils/guest_gate.dart';

/// Reprend Auth\GoogleController::setup() (route web) pour un nouveau compte
/// créé via "Continuer avec Google" — il ne manque que le pays et les CGU
/// (le rôle est fixé à "client" côté serveur, cette app étant réservée aux clients).
class GoogleSetupScreen extends StatefulWidget {
  final String googleId;
  final String googleName;
  final String googleEmail;
  const GoogleSetupScreen({super.key, required this.googleId, required this.googleName, required this.googleEmail});

  @override
  State<GoogleSetupScreen> createState() => _GoogleSetupScreenState();
}

class _GoogleSetupScreenState extends State<GoogleSetupScreen> {
  String _country = 'GN';
  bool _termsAccepted = false;
  bool _loading = false;
  String? _error;

  Future<void> _submit() async {
    if (!_termsAccepted) {
      setState(() => _error = "Vous devez accepter les conditions d'utilisation.");
      return;
    }
    setState(() { _loading = true; _error = null; });
    try {
      await context.read<AuthProvider>().completeGoogleSignup(
            googleId: widget.googleId,
            googleName: widget.googleName,
            googleEmail: widget.googleEmail,
            country: _country,
            terms: _termsAccepted,
          );
      if (mounted && Navigator.of(context).canPop()) Navigator.of(context).pop();
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Finaliser votre compte')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.check_circle_outline, color: Color(0xFF6366F1), size: 48),
              const SizedBox(height: 12),
              Text('Bienvenue, ${widget.googleName} 👋', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              Text(widget.googleEmail, style: const TextStyle(color: Colors.grey)),
              const SizedBox(height: 8),
              const Text('Plus qu\'une étape pour créer votre compte client Shopio.', style: TextStyle(color: Colors.grey, fontSize: 12.5)),
              const SizedBox(height: 24),
              DropdownButtonFormField<String>(
                initialValue: _country,
                decoration: const InputDecoration(labelText: 'Pays', border: OutlineInputBorder()),
                items: kCountries.entries.map((e) => DropdownMenuItem(value: e.key, child: Text('${e.value[0]} ${e.value[1]}'))).toList(),
                onChanged: (v) => setState(() => _country = v ?? 'GN'),
              ),
              const SizedBox(height: 14),
              CheckboxListTile(
                contentPadding: EdgeInsets.zero,
                controlAffinity: ListTileControlAffinity.leading,
                value: _termsAccepted,
                onChanged: (v) => setState(() => _termsAccepted = v ?? false),
                title: GestureDetector(
                  onTap: () => openExternalUrl(context, 'https://shopio-app.com/conditions-utilisation'),
                  child: const Text.rich(
                    TextSpan(
                      style: TextStyle(fontSize: 12.5),
                      children: [
                        TextSpan(text: "J'accepte les "),
                        TextSpan(text: "conditions d'utilisation et la politique de confidentialité", style: TextStyle(color: Color(0xFF6366F1), decoration: TextDecoration.underline)),
                        TextSpan(text: " de Shopio"),
                      ],
                    ),
                  ),
                ),
              ),
              if (_error != null) ...[
                const SizedBox(height: 12),
                Text(_error!, style: const TextStyle(color: Colors.red)),
              ],
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: _loading ? null : _submit,
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: _loading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Créer mon compte'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
