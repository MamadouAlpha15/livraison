import '../models/shop.dart';
import 'api_client.dart';

/// Boutiques suivies — distinct du wishlist produit (voir favorite_service.dart).
class ShopFavoriteService {
  final ApiClient _api;
  ShopFavoriteService(this._api);

  Future<List<FollowedShop>> list() async {
    final res = await _api.safeCall(() => _api.dio.get('/shop-favorites'));
    return (res.data['data'] as List).map((e) => FollowedShop.fromJson(e)).toList();
  }

  /// Retourne true si la boutique est maintenant suivie (false si elle vient d'être retirée).
  Future<bool> toggle(int shopId) async {
    final res = await _api.safeCall(() => _api.dio.post('/shops/$shopId/favorite'));
    return res.data['favorited'];
  }
}
