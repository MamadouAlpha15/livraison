import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/message.dart';
import '../../services/api_client.dart';
import '../../services/message_service.dart';
import '../../utils/formatters.dart';
import 'chat_screen.dart';

class ConversationsScreen extends StatefulWidget {
  const ConversationsScreen({super.key});
  @override
  State<ConversationsScreen> createState() => _ConversationsScreenState();
}

class _ConversationsScreenState extends State<ConversationsScreen> {
  late final MessageService _service = MessageService(context.read<ApiClient>());
  List<Conversation> _conversations = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final list = await _service.conversations();
      setState(() => _conversations = list);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Messages')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _conversations.isEmpty
              ? const Center(child: Text('Aucune conversation pour l\'instant.'))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    itemCount: _conversations.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (_, i) {
                      final c = _conversations[i];
                      return ListTile(
                        leading: CircleAvatar(backgroundColor: const Color(0xFFEEF2FF), child: const Icon(Icons.storefront, color: Color(0xFF6366F1))),
                        title: Text(c.shopName ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text('${c.productName ?? ""} — ${c.lastMessage ?? ""}', maxLines: 1, overflow: TextOverflow.ellipsis),
                        trailing: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            if (c.lastAt != null) Text(formatDateTime(c.lastAt!), style: const TextStyle(fontSize: 10, color: Colors.grey)),
                            if (c.unread > 0)
                              Container(
                                margin: const EdgeInsets.only(top: 4),
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(color: const Color(0xFF6366F1), borderRadius: BorderRadius.circular(20)),
                                child: Text('${c.unread}', style: const TextStyle(color: Colors.white, fontSize: 10)),
                              ),
                          ],
                        ),
                        onTap: () async {
                          await Navigator.of(context).push(MaterialPageRoute(
                            builder: (_) => ChatScreen(productId: c.productId, productName: c.productName ?? ''),
                          ));
                          _load();
                        },
                      );
                    },
                  ),
                ),
    );
  }
}
