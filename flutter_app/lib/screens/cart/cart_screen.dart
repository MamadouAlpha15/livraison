import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../services/api_client.dart';
import '../../utils/formatters.dart';
import '../orders/order_detail_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});
  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  bool _checkingOut = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => context.read<CartProvider>().refresh());
  }

  Future<void> _checkout() async {
    final addressCtrl = TextEditingController(text: context.read<AuthProvider>().user?.address ?? '');
    final phoneCtrl = TextEditingController(text: context.read<AuthProvider>().user?.phone ?? '');

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom, left: 20, right: 20, top: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Finaliser la commande', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            TextField(controller: addressCtrl, decoration: const InputDecoration(labelText: 'Adresse de livraison', border: OutlineInputBorder())),
            const SizedBox(height: 12),
            TextField(controller: phoneCtrl, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Téléphone', border: OutlineInputBorder())),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: () => Navigator.pop(ctx, true),
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
                child: const Text('Valider ma commande'),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );

    if (confirmed != true || !mounted) return;
    if (addressCtrl.text.trim().isEmpty || phoneCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Adresse et téléphone obligatoires.')));
      return;
    }

    setState(() => _checkingOut = true);
    try {
      final orderIds = await context.read<CartProvider>().checkout(
            deliveryDestination: addressCtrl.text.trim(),
            clientPhone: phoneCtrl.text.trim(),
          );
      if (!mounted) return;
      final msg = orderIds.length > 1 ? '${orderIds.length} commandes passées avec succès !' : 'Commande passée avec succès !';
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$msg 🎉')));
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: orderIds.first)));
    } on ApiException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _checkingOut = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mon panier')),
      body: Consumer<CartProvider>(
        builder: (context, cart, _) {
          if (cart.loading && cart.data.groups.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }
          if (cart.data.groups.isEmpty) {
            return const Center(child: Text('Votre panier est vide.'));
          }
          return Column(
            children: [
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.all(12),
                  children: cart.data.groups.map((group) => Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Padding(
                          padding: const EdgeInsets.all(10),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.storefront, size: 16, color: Color(0xFF6366F1)),
                                  const SizedBox(width: 6),
                                  Text(group.shopName, style: const TextStyle(fontWeight: FontWeight.bold)),
                                ],
                              ),
                              const Divider(),
                              ...group.items.map((item) => Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 4),
                                    child: Row(
                                      children: [
                                        if (item.imageUrl != null)
                                          ClipRRect(borderRadius: BorderRadius.circular(6), child: Image.network(item.imageUrl!, width: 44, height: 44, fit: BoxFit.cover)),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(item.productName, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                              if (item.variantName != null) Text(item.variantName!, style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                              Text('${formatPrice(item.unitPrice)} GNF', style: const TextStyle(fontSize: 12, color: Color(0xFF4F46E5))),
                                            ],
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.remove_circle_outline, size: 20),
                                          onPressed: item.quantity <= 1
                                              ? null
                                              : () => context.read<CartProvider>().updateQuantity(item.id, item.quantity - 1),
                                        ),
                                        Text('${item.quantity}'),
                                        IconButton(
                                          icon: const Icon(Icons.add_circle_outline, size: 20),
                                          onPressed: () => context.read<CartProvider>().updateQuantity(item.id, item.quantity + 1),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.delete_outline, size: 20, color: Colors.red),
                                          onPressed: () => context.read<CartProvider>().remove(item.id),
                                        ),
                                      ],
                                    ),
                                  )),
                              Align(
                                alignment: Alignment.centerRight,
                                child: Text('Sous-total : ${formatPrice(group.subtotal)} GNF', style: const TextStyle(fontWeight: FontWeight.bold)),
                              ),
                            ],
                          ),
                        ),
                      )).toList(),
                ),
              ),
              SafeArea(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Total', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                          Text('${formatPrice(cart.data.grandTotal)} GNF', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5))),
                        ],
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: _checkingOut ? null : _checkout,
                          style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
                          child: _checkingOut
                              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Text('Commander'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
