import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';

class OtpScreen extends StatefulWidget {
  final int userId;
  const OtpScreen({super.key, required this.userId});
  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final _codeCtrl = TextEditingController();
  bool _loading = false;
  bool _resending = false;
  String? _error;
  String? _status;

  Future<void> _verify() async {
    setState(() { _loading = true; _error = null; });
    try {
      await context.read<AuthProvider>().verifyOtp(userId: widget.userId, code: _codeCtrl.text.trim());
      // AuthProvider passe en loggedIn → AuthGate (main.dart) affiche l'app en dessous ;
      // si cet écran était empilé par-dessus (ex: depuis le mode invité), on le referme.
      if (mounted && Navigator.of(context).canPop()) Navigator.of(context).pop();
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _resend() async {
    setState(() { _resending = true; _status = null; _error = null; });
    try {
      await context.read<AuthProvider>().resendOtp(widget.userId);
      setState(() => _status = 'Un nouveau code a été envoyé.');
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _resending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vérification')),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.mark_email_read_outlined, size: 56, color: Color(0xFF6366F1)),
              const SizedBox(height: 16),
              const Text('Entrez le code reçu par email', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              const Text('Un code à 6 chiffres vous a été envoyé.', style: TextStyle(color: Colors.grey)),
              const SizedBox(height: 24),
              TextField(
                controller: _codeCtrl,
                keyboardType: TextInputType.number,
                maxLength: 6,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 24, letterSpacing: 8),
                decoration: const InputDecoration(counterText: '', border: OutlineInputBorder()),
              ),
              if (_error != null) Text(_error!, style: const TextStyle(color: Colors.red)),
              if (_status != null) Text(_status!, style: const TextStyle(color: Colors.green)),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: _loading ? null : _verify,
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: _loading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Confirmer'),
                ),
              ),
              TextButton(
                onPressed: _resending ? null : _resend,
                child: Text(_resending ? 'Envoi...' : 'Renvoyer le code'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
