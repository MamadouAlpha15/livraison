import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/product.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/order_service.dart';
import '../../utils/formatters.dart';
import '../orders/order_detail_screen.dart';

class DirectOrderScreen extends StatefulWidget {
  final Product product;
  final ProductVariant? variant;
  final int quantity;
  const DirectOrderScreen({super.key, required this.product, this.variant, required this.quantity});

  @override
  State<DirectOrderScreen> createState() => _DirectOrderScreenState();
}

class _DirectOrderScreenState extends State<DirectOrderScreen> {
  late final _nameCtrl = TextEditingController(text: context.read<AuthProvider>().user?.name ?? '');
  late final _addressCtrl = TextEditingController(text: context.read<AuthProvider>().user?.address ?? '');
  late final _phoneCtrl = TextEditingController(text: context.read<AuthProvider>().user?.phone ?? '');
  final _promoCtrl = TextEditingController();
  final _pointsCtrl = TextEditingController(text: '0');
  bool _usePoints = false;
  bool _loading = false;
  String? _error;

  static const _maxRedeemRatio = 0.5; // même règle que LoyaltyService::MAX_REDEEM_RATIO côté serveur

  double get _unitPrice => widget.variant?.price ?? widget.product.currentPrice;
  double get _subtotal => _unitPrice * widget.quantity;

  bool get _isGuest => context.read<AuthProvider>().user == null;
  int get _availablePoints => context.read<AuthProvider>().user?.loyaltyPoints ?? 0;
  int get _maxRedeemable => _usePoints ? [_availablePoints, (_subtotal * _maxRedeemRatio).floor()].reduce((a, b) => a < b ? a : b) : 0;

  int get _pointsUsed {
    if (!_usePoints) return 0;
    final entered = int.tryParse(_pointsCtrl.text) ?? 0;
    return entered.clamp(0, _maxRedeemable);
  }

  double get _total => (_subtotal - _pointsUsed).clamp(0, double.infinity);

  Future<void> _submit() async {
    if (_isGuest && _nameCtrl.text.trim().isEmpty) {
      setState(() => _error = 'Le nom complet est obligatoire.');
      return;
    }
    setState(() { _loading = true; _error = null; });
    try {
      final orderId = await OrderService(context.read<ApiClient>()).storeDirect(
        productId: widget.product.id,
        variantId: widget.variant?.id,
        quantity: widget.quantity,
        deliveryDestination: _addressCtrl.text.trim(),
        clientPhone: _phoneCtrl.text.trim(),
        promoCode: _promoCtrl.text.trim(),
        pointsToUse: _pointsUsed,
        clientName: _isGuest ? _nameCtrl.text.trim() : null,
      );
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: orderId)));
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Commande passée avec succès ! 🎉')));
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final currency = widget.product.shop?.currency ?? 'GNF';
    return Scaffold(
      appBar: AppBar(title: const Text('Commander')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
                child: ListTile(
                  title: Text(widget.product.name),
                  subtitle: Text(
                    '${widget.quantity} x ${formatPrice(_unitPrice)} $currency'
                    '${widget.variant != null ? ' • ${widget.variant!.name}' : ''}',
                  ),
                  trailing: Text('${formatPrice(_subtotal)} $currency', style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(height: 16),
              if (_isGuest) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  margin: const EdgeInsets.only(bottom: 14),
                  decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8)),
                  child: const Row(
                    children: [
                      Icon(Icons.info_outline, color: Colors.blue, size: 18),
                      SizedBox(width: 8),
                      Expanded(child: Text('Vous commandez sans compte — indiquez votre nom complet pour la livraison.', style: TextStyle(color: Colors.blue, fontSize: 12))),
                    ],
                  ),
                ),
                TextField(controller: _nameCtrl, decoration: const InputDecoration(labelText: 'Nom complet', border: OutlineInputBorder())),
                const SizedBox(height: 14),
              ],
              TextField(controller: _addressCtrl, decoration: const InputDecoration(labelText: 'Adresse de livraison', border: OutlineInputBorder())),
              const SizedBox(height: 14),
              TextField(controller: _phoneCtrl, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Téléphone', border: OutlineInputBorder())),
              const SizedBox(height: 14),
              TextField(controller: _promoCtrl, decoration: const InputDecoration(labelText: 'Code promo (optionnel)', border: OutlineInputBorder())),

              if (!_isGuest && _availablePoints > 0) ...[
                const SizedBox(height: 14),
                CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  controlAffinity: ListTileControlAffinity.leading,
                  value: _usePoints,
                  onChanged: (v) => setState(() {
                    _usePoints = v ?? false;
                    if (_usePoints) _pointsCtrl.text = '$_maxRedeemable';
                  }),
                  title: Text('🎁 Utiliser mes points (solde : ${formatPrice(_availablePoints)})', style: const TextStyle(fontSize: 13)),
                ),
                if (_usePoints)
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _pointsCtrl,
                          keyboardType: TextInputType.number,
                          onChanged: (_) => setState(() {}),
                          decoration: InputDecoration(labelText: 'Points à utiliser', helperText: 'Max ${formatPrice(_maxRedeemable)} (50% de la commande)', border: const OutlineInputBorder()),
                        ),
                      ),
                      const SizedBox(width: 8),
                      OutlinedButton(onPressed: () => setState(() => _pointsCtrl.text = '$_maxRedeemable'), child: const Text('MAX')),
                    ],
                  ),
              ],

              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(8)),
                child: Row(
                  children: const [
                    Icon(Icons.payments_outlined, color: Colors.green, size: 18),
                    SizedBox(width: 8),
                    Expanded(child: Text('Cash à la livraison — aucune carte requise', style: TextStyle(color: Colors.green))),
                  ],
                ),
              ),

              if (_pointsUsed > 0) ...[
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Text('Réduction points', style: TextStyle(color: Colors.green)),
                    const Spacer(),
                    Text('-${formatPrice(_pointsUsed)}', style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
                  ],
                ),
              ],
              const SizedBox(height: 6),
              Row(
                children: [
                  const Text('Total', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const Spacer(),
                  Text('${formatPrice(_total)} $currency', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Color(0xFF4F46E5))),
                ],
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
                      : const Text('Valider ma commande'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
