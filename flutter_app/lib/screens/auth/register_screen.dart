import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../utils/countries.dart';
import '../../utils/guest_gate.dart';
import 'otp_screen.dart';

/// Reprend le design en 2 étapes de resources/views/auth/register.blade.php
/// (identité → coordonnées) — le rôle est fixé à "client" (cette app est
/// réservée aux clients ; les comptes vendeur/entreprise de livraison restent
/// sur le site web, une app dédiée est prévue plus tard).
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  int _step = 1;

  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _passwordConfirmCtrl = TextEditingController();
  String _country = 'GN';
  bool _termsAccepted = false;
  bool _obscurePw = true;
  bool _obscureConfirm = true;
  bool _loading = false;
  String? _error;
  String? _step1Error;

  bool _validateStep1() {
    if (_nameCtrl.text.trim().isEmpty) return _fail1('Le nom est requis.');
    if (!_emailCtrl.text.contains('@') || !_emailCtrl.text.contains('.')) return _fail1('Entrez une adresse email valide.');
    if (_passwordCtrl.text.length < 8) return _fail1('Minimum 8 caractères requis.');
    if (_passwordConfirmCtrl.text != _passwordCtrl.text || _passwordConfirmCtrl.text.isEmpty) {
      return _fail1('Les mots de passe ne correspondent pas.');
    }
    return true;
  }

  bool _fail1(String msg) {
    setState(() => _step1Error = msg);
    return false;
  }

  void _goStep2() {
    if (!_validateStep1()) return;
    setState(() { _step1Error = null; _step = 2; });
  }

  Future<void> _submit() async {
    if (!_termsAccepted) {
      setState(() => _error = "Vous devez accepter les conditions d'utilisation.");
      return;
    }
    setState(() { _loading = true; _error = null; });
    try {
      final userId = await context.read<AuthProvider>().register(
            name: _nameCtrl.text.trim(),
            email: _emailCtrl.text.trim(),
            password: _passwordCtrl.text,
            passwordConfirmation: _passwordConfirmCtrl.text,
            country: _country,
            terms: _termsAccepted,
            phone: _phoneCtrl.text.trim().isEmpty ? null : _phoneCtrl.text.trim(),
            address: _addressCtrl.text.trim().isEmpty ? null : _addressCtrl.text.trim(),
          );
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => OtpScreen(userId: userId)));
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Créer un compte')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _Stepper(step: _step),
              const SizedBox(height: 24),
              if (_step == 1) _buildStep1() else _buildStep2(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStep1() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Je m\'inscris en tant que', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
        const SizedBox(height: 8),
        Row(
          children: [
            const _RoleCard(icon: '🛒', label: 'Client', active: true),
            const SizedBox(width: 8),
            Expanded(child: _RoleCard(icon: '🏪', label: 'Admin boutique', active: false, onTap: () => _showRoleComingSoon('vendeur'))),
            const SizedBox(width: 8),
            Expanded(child: _RoleCard(icon: '🚚', label: 'Entreprise', active: false, onTap: () => _showRoleComingSoon('entreprise de livraison'))),
          ],
        ),
        const SizedBox(height: 4),
        Text('Les comptes vendeur/entreprise de livraison restent sur le site web pour l\'instant.', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
        const SizedBox(height: 18),
        TextField(controller: _nameCtrl, decoration: const InputDecoration(labelText: 'Nom complet', border: OutlineInputBorder())),
        const SizedBox(height: 14),
        TextField(controller: _emailCtrl, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder())),
        const SizedBox(height: 14),
        TextField(
          controller: _passwordCtrl,
          obscureText: _obscurePw,
          decoration: InputDecoration(
            labelText: 'Mot de passe', border: const OutlineInputBorder(),
            helperText: 'Minimum 8 caractères',
            suffixIcon: IconButton(icon: Icon(_obscurePw ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _obscurePw = !_obscurePw)),
          ),
        ),
        const SizedBox(height: 14),
        TextField(
          controller: _passwordConfirmCtrl,
          obscureText: _obscureConfirm,
          decoration: InputDecoration(
            labelText: 'Confirmer le mot de passe', border: const OutlineInputBorder(),
            suffixIcon: IconButton(icon: Icon(_obscureConfirm ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _obscureConfirm = !_obscureConfirm)),
          ),
        ),
        if (_step1Error != null) ...[
          const SizedBox(height: 10),
          Text(_step1Error!, style: const TextStyle(color: Colors.red, fontSize: 12.5)),
        ],
        const SizedBox(height: 20),
        SizedBox(
          width: double.infinity,
          child: FilledButton(
            onPressed: _goStep2,
            style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
            child: const Text('Suivant →'),
          ),
        ),
        const SizedBox(height: 18),
        Row(children: const [Expanded(child: Divider()), Padding(padding: EdgeInsets.symmetric(horizontal: 10), child: Text('ou', style: TextStyle(color: Colors.grey))), Expanded(child: Divider())]),
        const SizedBox(height: 14),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => openExternalUrl(context, 'https://shopio-app.com/auth/google/mobile'),
            icon: const Icon(Icons.g_mobiledata, size: 26, color: Colors.redAccent),
            label: const Text("S'inscrire avec Google"),
            style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12)),
          ),
        ),
      ],
    );
  }

  Widget _buildStep2() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('🌍 Votre pays', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
        const SizedBox(height: 8),
        DropdownButtonFormField<String>(
          initialValue: _country,
          decoration: const InputDecoration(border: OutlineInputBorder()),
          isExpanded: true,
          items: kCountries.entries.map((e) => DropdownMenuItem(value: e.key, child: Text('${e.value[0]} ${e.value[1]}'))).toList(),
          onChanged: (v) => setState(() => _country = v ?? 'GN'),
        ),
        const SizedBox(height: 4),
        Text('Vous verrez uniquement les boutiques disponibles dans votre pays.', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
        const SizedBox(height: 16),
        TextField(controller: _phoneCtrl, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Téléphone', hintText: '+224 6XX XX XX XX', border: OutlineInputBorder())),
        const SizedBox(height: 14),
        TextField(controller: _addressCtrl, decoration: const InputDecoration(labelText: 'Adresse de livraison', border: OutlineInputBorder())),
        const SizedBox(height: 16),
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
          const SizedBox(height: 10),
          Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 12.5)),
        ],
        const SizedBox(height: 16),
        Row(
          children: [
            OutlinedButton(onPressed: () => setState(() => _step = 1), child: const Text('← Retour')),
            const SizedBox(width: 10),
            Expanded(
              child: FilledButton(
                onPressed: _loading ? null : _submit,
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF10B981), padding: const EdgeInsets.symmetric(vertical: 14)),
                child: _loading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Créer mon compte ✓'),
              ),
            ),
          ],
        ),
      ],
    );
  }

  void _showRoleComingSoon(String role) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Les comptes $role arrivent bientôt sur l\'app — pour l\'instant, inscrivez-vous sur shopio-app.com.')));
  }
}

class _RoleCard extends StatelessWidget {
  final String icon;
  final String label;
  final bool active;
  final VoidCallback? onTap;
  const _RoleCard({required this.icon, required this.label, required this.active, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: active ? const Color(0xFFEEF2FF) : Colors.grey.shade50,
          border: Border.all(color: active ? const Color(0xFF6366F1) : Colors.grey.shade300, width: active ? 1.6 : 1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(icon, style: const TextStyle(fontSize: 20)),
            const SizedBox(height: 4),
            Text(label, textAlign: TextAlign.center, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: active ? const Color(0xFF4F46E5) : Colors.grey.shade500)),
          ],
        ),
      ),
    );
  }
}

class _Stepper extends StatelessWidget {
  final int step;
  const _Stepper({required this.step});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _dot('1', step >= 1, step > 1),
        const SizedBox(width: 4),
        Text('Identité', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: step == 1 ? const Color(0xFF6366F1) : Colors.grey)),
        Container(width: 40, height: 2, margin: const EdgeInsets.symmetric(horizontal: 8), color: step > 1 ? const Color(0xFF10B981) : Colors.grey.shade300),
        _dot('2', step >= 2, false),
        const SizedBox(width: 4),
        Text('Coordonnées', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: step == 2 ? const Color(0xFF6366F1) : Colors.grey)),
      ],
    );
  }

  Widget _dot(String label, bool active, bool done) {
    return Container(
      width: 26, height: 26,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: done ? const Color(0xFF10B981) : (active ? const Color(0xFF6366F1) : Colors.grey.shade200),
      ),
      child: Text(done ? '✓' : label, style: TextStyle(color: (active || done) ? Colors.white : Colors.grey.shade500, fontSize: 12, fontWeight: FontWeight.bold)),
    );
  }
}
