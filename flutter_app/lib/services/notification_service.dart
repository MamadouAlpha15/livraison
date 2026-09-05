import '../models/notification_data.dart';
import 'api_client.dart';

class NotificationService {
  final ApiClient _api;
  NotificationService(this._api);

  Future<NotificationFeed> feed() async {
    final res = await _api.safeCall(() => _api.dio.get('/notifications'));
    return NotificationFeed.fromJson(res.data['data']);
  }

  Future<void> markOrdersSeen() async {
    await _api.safeCall(() => _api.dio.post('/notifications/mark-orders-seen'));
  }
}
