import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/notification_data.dart';
import '../../models/order.dart';
import '../../services/api_client.dart';
import '../../services/notification_service.dart';
import '../../utils/formatters.dart';
import '../messages/chat_screen.dart';
import '../orders/order_detail_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  late final NotificationService _service = NotificationService(context.read<ApiClient>());
  NotificationFeed _feed = NotificationFeed.empty();
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final feed = await _service.feed();
      setState(() => _feed = feed);
      if (feed.orderUpdatesUnseen > 0) _service.markOrdersSeen();
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'livrée': return Colors.green;
      case 'en_livraison': return Colors.blue;
      default: return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    final hasAny = _feed.latestMessages.isNotEmpty || _feed.orderUpdates.isNotEmpty;
    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : !hasAny
              ? const Center(child: Text('Aucune notification.'))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    children: [
                      if (_feed.latestMessages.isNotEmpty) ...[
                        const _SectionHeader(title: 'Messages'),
                        ..._feed.latestMessages.map((m) => ListTile(
                              leading: const CircleAvatar(backgroundColor: Color(0xFFEEF2FF), child: Icon(Icons.chat_bubble_outline, color: Color(0xFF6366F1))),
                              title: Text(m.senderName, style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text('${m.productName ?? ""} — ${m.body}', maxLines: 2, overflow: TextOverflow.ellipsis),
                              trailing: m.createdAt != null ? Text(formatDateTime(m.createdAt!), style: const TextStyle(fontSize: 10, color: Colors.grey)) : null,
                              onTap: m.productId != null
                                  ? () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ChatScreen(productId: m.productId!, productName: m.productName ?? '')))
                                  : null,
                            )),
                      ],
                      if (_feed.orderUpdates.isNotEmpty) ...[
                        const _SectionHeader(title: 'Commandes'),
                        ..._feed.orderUpdates.map((o) => ListTile(
                              leading: CircleAvatar(backgroundColor: _statusColor(o.status).withValues(alpha: .12), child: Icon(Icons.local_shipping_outlined, color: _statusColor(o.status))),
                              title: Text(o.shopName, style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text('${OrderStatus.label(o.status)} • ${formatPrice(o.total)} GNF'),
                              trailing: o.updatedAt != null ? Text(formatDateTime(o.updatedAt!), style: const TextStyle(fontSize: 10, color: Colors.grey)) : null,
                              onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: o.id))),
                            )),
                      ],
                    ],
                  ),
                ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 6),
      child: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.grey)),
    );
  }
}
