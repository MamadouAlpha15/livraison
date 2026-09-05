import '../models/home_dashboard.dart';
import 'api_client.dart';

class HomeService {
  final ApiClient _api;
  HomeService(this._api);

  Future<HomeDashboard> dashboard() async {
    final res = await _api.safeCall(() => _api.dio.get('/home'));
    return HomeDashboard.fromJson(res.data['data']);
  }
}
