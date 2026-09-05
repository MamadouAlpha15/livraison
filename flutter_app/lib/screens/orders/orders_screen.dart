import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/order.dart';
import '../../services/api_client.dart';
import '../../services/order_service.dart';
import '../../utils/formatters.dart';
import 'order_detail_screen.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});
  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  late final OrderService _service = OrderService(context.read<ApiClient>());
  List<OrderSummary> _orders = [];
  bool _loading = true;
  String _status = 'all';

  final _tabs = const {
    'all': 'Toutes',
    'en_attente': 'En attente',
    'en_livraison': 'En livraison',
    'livrée': 'Livrées',
    'annulée': 'Annulées',
  };

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final orders = await _service.list(status: _status);
      setState(() => _orders = orders);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'livrée': return Colors.green;
      case 'annulée': return Colors.red;
      case 'en_livraison': return Colors.blue;
      default: return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mes commandes')),
      body: Column(
        children: [
          SizedBox(
            height: 44,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              children: _tabs.entries.map((e) => Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(e.value),
                      selected: _status == e.key,
                      onSelected: (_) { setState(() => _status = e.key); _load(); },
                      selectedColor: const Color(0xFF6366F1),
                      labelStyle: TextStyle(color: _status == e.key ? Colors.white : Colors.black87, fontSize: 12.5),
                    ),
                  )).toList(),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _orders.isEmpty
                    ? const Center(child: Text('Aucune commande.'))
                    : RefreshIndicator(
                        onRefresh: _load,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: _orders.length,
                          itemBuilder: (_, i) {
                            final o = _orders[i];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: o.id))),
                                title: Text(o.shopName, style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text('${o.itemsCount} article(s) • ${o.createdAt != null ? formatDateTime(o.createdAt!) : ''}'),
                                trailing: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    Text('${formatPrice(o.total)} GNF', style: const TextStyle(fontWeight: FontWeight.bold)),
                                    const SizedBox(height: 4),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(color: _statusColor(o.status).withValues(alpha: .12), borderRadius: BorderRadius.circular(20)),
                                      child: Text(OrderStatus.label(o.status), style: TextStyle(color: _statusColor(o.status), fontSize: 10.5, fontWeight: FontWeight.w600)),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
