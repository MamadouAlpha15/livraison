import '../models/message.dart';
import 'api_client.dart';

class MessageService {
  final ApiClient _api;
  MessageService(this._api);

  Future<List<Conversation>> conversations() async {
    final res = await _api.safeCall(() => _api.dio.get('/messages'));
    return (res.data['data'] as List).map((e) => Conversation.fromJson(e)).toList();
  }

  Future<List<ChatMessage>> thread(int productId, {bool poll = false}) async {
    final res = await _api.safeCall(
      () => _api.dio.get('/products/$productId/messages', queryParameters: poll ? {'poll': 1} : null),
    );
    return (res.data['data'] as List).map((e) => ChatMessage.fromJson(e)).toList();
  }

  Future<void> send(int productId, String body) async {
    await _api.safeCall(() => _api.dio.post('/products/$productId/messages', data: {'body': body}));
  }

  Future<void> proposePrice({required int productId, required double price, String? message}) async {
    await _api.safeCall(() => _api.dio.post('/messages/propose-price', data: {
          'product_id': productId,
          'proposed_price': price,
          if (message != null && message.isNotEmpty) 'message': message,
        }));
  }

  Future<int> confirmOffer(int messageId) async {
    final res = await _api.safeCall(() => _api.dio.post('/messages/$messageId/confirm-offer'));
    return res.data['order_id'];
  }

  Future<void> refuseOffer(int messageId) async {
    await _api.safeCall(() => _api.dio.post('/messages/$messageId/refuse-offer'));
  }

  Future<void> counterOffer({required int messageId, required double price, String? message}) async {
    await _api.safeCall(() => _api.dio.post('/messages/counter-offer', data: {
          'message_id': messageId,
          'counter_price': price,
          if (message != null && message.isNotEmpty) 'message': message,
        }));
  }
}
