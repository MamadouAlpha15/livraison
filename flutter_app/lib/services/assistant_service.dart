import '../models/assistant_message.dart';
import 'api_client.dart';

class AssistantReply {
  final String reply;
  final List<AssistantProduct> products;
  AssistantReply({required this.reply, required this.products});
}

class AssistantService {
  final ApiClient _api;
  AssistantService(this._api);

  Future<AssistantReply> chat({required String message, required List<AssistantMessage> history}) async {
    final res = await _api.safeCall(() => _api.dio.post('/assistant/chat', data: {
          'message': message,
          'history': history.map((m) => m.toHistoryJson()).toList(),
        }));
    return AssistantReply(
      reply: res.data['reply'] ?? '',
      products: (res.data['products'] as List? ?? []).map((e) => AssistantProduct.fromJson(e)).toList(),
    );
  }
}
