import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import '../../models/order.dart';
import '../../services/api_client.dart';
import '../../services/order_service.dart';
import '../../utils/formatters.dart';

class OrderDetailScreen extends StatefulWidget {
  final int orderId;
  const OrderDetailScreen({super.key, required this.orderId});
  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  late final OrderService _service = OrderService(context.read<ApiClient>());
  OrderDetail? _order;
  bool _loading = true;
  bool _downloadingInvoice = false;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final o = await _service.show(widget.orderId);
      setState(() => _order = o);
      _schedulePoll();
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  /// Tant que la commande est en livraison, on rafraîchit la position du
  /// livreur toutes les 15s (même principe que le polling de la carte sur le site).
  void _schedulePoll() {
    _pollTimer?.cancel();
    if (_order?.status != 'en_livraison') return;
    _pollTimer = Timer(const Duration(seconds: 15), () async {
      try {
        final o = await _service.show(widget.orderId);
        if (mounted) setState(() => _order = o);
      } catch (_) {}
      _schedulePoll();
    });
  }

  Future<void> _downloadInvoice() async {
    setState(() => _downloadingInvoice = true);
    try {
      final bytes = await _service.downloadInvoice(widget.orderId);
      final dir = await getTemporaryDirectory();
      final file = File('${dir.path}/Recu-Shopio-Commande-${widget.orderId}.pdf');
      await file.writeAsBytes(bytes, flush: true);
      await OpenFilex.open(file.path);
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _downloadingInvoice = false);
    }
  }

  Future<void> _openReviewDialog() async {
    int rating = 5;
    final commentCtrl = TextEditingController();
    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: const Text('Laisser un avis'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(5, (i) => IconButton(
                      icon: Icon(i < rating ? Icons.star : Icons.star_border, color: Colors.amber),
                      onPressed: () => setDialogState(() => rating = i + 1),
                    )),
              ),
              TextField(
                controller: commentCtrl,
                decoration: const InputDecoration(labelText: 'Commentaire (optionnel)', border: OutlineInputBorder()),
                maxLines: 3,
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
            FilledButton(
              onPressed: () async {
                Navigator.pop(ctx);
                try {
                  await _service.submitReview(widget.orderId, rating: rating, comment: commentCtrl.text.trim());
                  if (!mounted) return;
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Merci pour votre avis ✅')));
                } on ApiException catch (e) {
                  if (!mounted) return;
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
                }
              },
              child: const Text('Envoyer'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_order == null) return Scaffold(appBar: AppBar(), body: const Center(child: Text('Commande introuvable.')));

    final o = _order!;
    return Scaffold(
      appBar: AppBar(
        title: Text('Commande #${o.id}'),
        actions: [
          IconButton(
            onPressed: _downloadingInvoice ? null : _downloadInvoice,
            icon: _downloadingInvoice
                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.receipt_long_outlined),
            tooltip: 'Télécharger le reçu',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(child: Text(o.shop.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16))),
                        Chip(label: Text(OrderStatus.label(o.status))),
                      ],
                    ),
                    if (o.createdAt != null) Text('Passée le ${formatDateTime(o.createdAt!)}', style: const TextStyle(color: Colors.grey)),
                  ],
                ),
              ),
            ),
            if (o.tracking != null) ...[
              const SizedBox(height: 12),
              _TrackingMap(tracking: o.tracking!, livreurName: o.livreurName, livreurPhone: o.livreurPhone),
            ],
            const SizedBox(height: 16),
            const Text('Articles', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 8),
            ...o.items.map((it) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: it.imageUrl != null
                      ? ClipRRect(borderRadius: BorderRadius.circular(6), child: Image.network(it.imageUrl!, width: 44, height: 44, fit: BoxFit.cover))
                      : Container(width: 44, height: 44, decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(6))),
                  title: Text(it.productName),
                  subtitle: Text('${it.quantity} x ${formatPrice(it.price)} GNF'),
                  trailing: Text('${formatPrice(it.subtotal)} GNF', style: const TextStyle(fontWeight: FontWeight.bold)),
                )),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Total', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                Text('${formatPrice(o.total)} GNF', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF4F46E5))),
              ],
            ),
            const SizedBox(height: 16),
            if (o.deliveryDestination != null) _InfoRow(icon: Icons.location_on_outlined, label: o.deliveryDestination!),
            if (o.clientPhone != null) _InfoRow(icon: Icons.phone_outlined, label: o.clientPhone!),
            _InfoRow(icon: Icons.payments_outlined, label: o.paymentMethod == 'cash' ? 'Paiement en espèces' : (o.paymentMethod ?? '')),
            if (o.deliveryProofPhotoUrl != null) ...[
              const SizedBox(height: 16),
              const Text('Preuve de livraison', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              const SizedBox(height: 8),
              ClipRRect(borderRadius: BorderRadius.circular(10), child: Image.network(o.deliveryProofPhotoUrl!)),
            ],
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _downloadingInvoice ? null : _downloadInvoice,
                icon: const Icon(Icons.download_outlined),
                label: const Text('Télécharger le reçu (PDF)'),
              ),
            ),
            if (o.status == 'livrée') ...[
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _openReviewDialog,
                  icon: const Icon(Icons.star_border),
                  label: const Text('Laisser un avis'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Position du livreur en direct — même principe que la carte Leaflet du site
/// (fallback de fournisseurs de tuiles), simplifié avec OpenStreetMap.
class _TrackingMap extends StatelessWidget {
  final OrderTracking tracking;
  final String? livreurName;
  final String? livreurPhone;
  const _TrackingMap({required this.tracking, this.livreurName, this.livreurPhone});

  @override
  Widget build(BuildContext context) {
    final point = LatLng(tracking.lat, tracking.lng);
    return Card(
      color: Colors.blue.shade50,
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                const Icon(Icons.local_shipping_outlined, color: Colors.blue),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Livreur en route', style: TextStyle(fontWeight: FontWeight.bold)),
                      if (livreurName != null) Text(livreurName!),
                      if (livreurPhone != null) Text(livreurPhone!, style: const TextStyle(color: Colors.grey)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          SizedBox(
            height: 200,
            child: FlutterMap(
              options: MapOptions(initialCenter: point, initialZoom: 15),
              children: [
                TileLayer(
                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                  userAgentPackageName: 'com.shopio.app',
                ),
                MarkerLayer(markers: [
                  Marker(
                    point: point,
                    width: 40,
                    height: 40,
                    child: const Icon(Icons.delivery_dining, color: Color(0xFF4F46E5), size: 36),
                  ),
                ]),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  const _InfoRow({required this.icon, required this.label});
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey),
          const SizedBox(width: 8),
          Expanded(child: Text(label)),
        ],
      ),
    );
  }
}
