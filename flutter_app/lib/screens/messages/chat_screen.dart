import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/message.dart';
import '../../services/api_client.dart';
import '../../services/message_service.dart';
import '../../utils/formatters.dart';
import '../orders/order_detail_screen.dart';

class ChatScreen extends StatefulWidget {
  final int productId;
  final String productName;
  const ChatScreen({super.key, required this.productId, required this.productName});
  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  late final MessageService _service = MessageService(context.read<ApiClient>());
  final _bodyCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  List<ChatMessage> _messages = [];
  bool _loading = true;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _load();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) => _load(poll: true));
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool poll = false}) async {
    try {
      final msgs = await _service.thread(widget.productId, poll: poll);
      if (!mounted) return;
      setState(() { _messages = msgs; _loading = false; });
      if (!poll) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (_scrollCtrl.hasClients) _scrollCtrl.jumpTo(_scrollCtrl.position.maxScrollExtent);
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _send() async {
    final text = _bodyCtrl.text.trim();
    if (text.isEmpty) return;
    _bodyCtrl.clear();
    try {
      await _service.send(widget.productId, text);
      await _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _proposePrice() async {
    final priceCtrl = TextEditingController();
    final msgCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Proposer un prix'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: priceCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Votre prix (GNF)', border: OutlineInputBorder())),
            const SizedBox(height: 10),
            TextField(controller: msgCtrl, decoration: const InputDecoration(labelText: 'Message (optionnel)', border: OutlineInputBorder())),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Envoyer')),
        ],
      ),
    );
    if (ok != true) return;
    final price = double.tryParse(priceCtrl.text.trim());
    if (price == null || price <= 0) return;
    try {
      await _service.proposePrice(productId: widget.productId, price: price, message: msgCtrl.text.trim());
      await _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _acceptOffer(ChatMessage m) async {
    try {
      final orderId = await _service.confirmOffer(m.id);
      await _load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Commande créée ✅')));
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: orderId)));
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _refuseOffer(ChatMessage m) async {
    try {
      await _service.refuseOffer(m.id);
      await _load();
    } on ApiException catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.productName, maxLines: 1, overflow: TextOverflow.ellipsis)),
      body: Column(
        children: [
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollCtrl,
                    padding: const EdgeInsets.all(12),
                    itemCount: _messages.length,
                    itemBuilder: (_, i) => _MessageBubble(
                      message: _messages[i],
                      onAccept: () => _acceptOffer(_messages[i]),
                      onRefuse: () => _refuseOffer(_messages[i]),
                    ),
                  ),
          ),
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              child: Row(
                children: [
                  IconButton(
                    onPressed: _proposePrice,
                    icon: const Icon(Icons.sell_outlined, color: Color(0xFF6366F1)),
                    tooltip: 'Proposer un prix',
                  ),
                  Expanded(
                    child: TextField(
                      controller: _bodyCtrl,
                      decoration: InputDecoration(
                        hintText: 'Écrire un message...',
                        filled: true,
                        fillColor: Colors.grey.shade100,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                      ),
                      onSubmitted: (_) => _send(),
                    ),
                  ),
                  const SizedBox(width: 6),
                  IconButton.filled(
                    onPressed: _send,
                    icon: const Icon(Icons.send),
                    style: IconButton.styleFrom(backgroundColor: const Color(0xFF6366F1)),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  final ChatMessage message;
  final VoidCallback onAccept;
  final VoidCallback onRefuse;
  const _MessageBubble({required this.message, required this.onAccept, required this.onRefuse});

  @override
  Widget build(BuildContext context) {
    final isOffer = (message.type == 'price_offer' || message.type == 'price_counter') &&
        message.proposalStatus == 'pending' && !message.mine;

    return Align(
      alignment: message.mine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * .78),
        decoration: BoxDecoration(
          color: message.mine ? const Color(0xFF6366F1) : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (message.images.isNotEmpty)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(message.images.first, width: 180, fit: BoxFit.cover),
              )
            else
              Text(message.body ?? '', style: TextStyle(color: message.mine ? Colors.white : Colors.black87)),
            if (message.createdAt != null)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(
                  formatDateTime(message.createdAt!),
                  style: TextStyle(fontSize: 10, color: message.mine ? Colors.white70 : Colors.grey),
                ),
              ),
            if (isOffer) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(child: OutlinedButton(onPressed: onRefuse, child: const Text('Refuser'))),
                  const SizedBox(width: 8),
                  Expanded(child: FilledButton(onPressed: onAccept, child: const Text('Accepter'))),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}
