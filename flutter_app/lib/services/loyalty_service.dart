import '../models/loyalty.dart';
import 'api_client.dart';

class LoyaltyService {
  final ApiClient _api;
  LoyaltyService(this._api);

  Future<LoyaltyData> get() async {
    final res = await _api.safeCall(() => _api.dio.get('/loyalty'));
    return LoyaltyData.fromJson(res.data['data']);
  }
}
