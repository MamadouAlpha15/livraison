import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/assistant_message.dart';
import '../../services/api_client.dart';
import '../../services/assistant_service.dart';
import '../../utils/formatters.dart';
import '../product/product_detail_screen.dart';

class AssistantScreen extends StatefulWidget {
  const AssistantScreen({super.key});
  @override
  State<AssistantScreen> createState() => _AssistantScreenState();
}

class _AssistantScreenState extends State<AssistantScreen> {
  late final AssistantService _service = AssistantService(context.read<ApiClient>());
  final _inputCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  final List<AssistantMessage> _messages = [
    AssistantMessage(
      role: 'assistant',
      content: "Bonjour 👋 Je suis l'assistant Shopio. Dites-moi ce que vous cherchez (un produit, un budget, une catégorie…) et je vous aide à le trouver !",
    ),
  ];
  bool _sending = false;

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollCtrl.hasClients) _scrollCtrl.animateTo(_scrollCtrl.position.maxScrollExtent, duration: const Duration(milliseconds: 250), curve: Curves.easeOut);
    });
  }

  Future<void> _send() async {
    final text = _inputCtrl.text.trim();
    if (text.isEmpty || _sending) return;
    _inputCtrl.clear();
    setState(() {
      _messages.add(AssistantMessage(role: 'user', content: text));
      _sending = true;
    });
    _scrollToBottom();
    try {
      // On envoie tout l'historique SAUF le message d'accueil fixe (jamais réellement dit par l'IA côté serveur).
      final history = _messages.length > 1 ? _messages.sublist(1, _messages.length - 1) : <AssistantMessage>[];
      final reply = await _service.chat(message: text, history: history);
      setState(() => _messages.add(AssistantMessage(role: 'assistant', content: reply.reply, products: reply.products)));
    } on ApiException catch (e) {
      setState(() => _messages.add(AssistantMessage(role: 'assistant', content: e.message)));
    } finally {
      if (mounted) setState(() => _sending = false);
      _scrollToBottom();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Row(
          children: [
            Icon(Icons.auto_awesome, size: 20),
            SizedBox(width: 8),
            Text('Assistant Shopio'),
          ],
        ),
        backgroundColor: const Color(0xFF6366F1),
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollCtrl,
              padding: const EdgeInsets.all(14),
              itemCount: _messages.length + (_sending ? 1 : 0),
              itemBuilder: (_, i) {
                if (i >= _messages.length) {
                  return const Align(alignment: Alignment.centerLeft, child: Padding(padding: EdgeInsets.symmetric(vertical: 6), child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))));
                }
                return _AssistantBubble(message: _messages[i]);
              },
            ),
          ),
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _inputCtrl,
                      decoration: InputDecoration(
                        hintText: 'Ex: montre pas chère pour homme',
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
                    onPressed: _sending ? null : _send,
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

class _AssistantBubble extends StatelessWidget {
  final AssistantMessage message;
  const _AssistantBubble({required this.message});

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: message.isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Column(
        crossAxisAlignment: message.isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.symmetric(vertical: 4),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * .8),
            decoration: BoxDecoration(
              color: message.isUser ? const Color(0xFF6366F1) : Colors.grey.shade100,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Text(message.content, style: TextStyle(color: message.isUser ? Colors.white : Colors.black87)),
          ),
          if (message.products.isNotEmpty)
            SizedBox(
              height: 150,
              width: MediaQuery.of(context).size.width * .85,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: message.products.length,
                itemBuilder: (_, i) {
                  final p = message.products[i];
                  return GestureDetector(
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ProductDetailScreen(productId: p.id))),
                    child: Container(
                      width: 120,
                      margin: const EdgeInsets.only(right: 8, bottom: 4),
                      decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade300), borderRadius: BorderRadius.circular(10)),
                      clipBehavior: Clip.antiAlias,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: p.image != null
                                ? Image.network(p.image!, fit: BoxFit.cover, width: double.infinity)
                                : Container(color: Colors.grey.shade100, child: const Icon(Icons.shopping_bag_outlined, color: Colors.grey)),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(6),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
                                Text('${formatPrice(p.price)} ${p.currency}', style: const TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
