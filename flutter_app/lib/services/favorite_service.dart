import '../models/product.dart';
import 'api_client.dart';

class FavoriteService {
  final ApiClient _api;
  FavoriteService(this._api);

  Future<List<Product>> list() async {
    final res = await _api.safeCall(() => _api.dio.get('/favorites'));
    return (res.data['data'] as List).map((e) => Product.fromJson(e)).toList();
  }

  /// Retourne true si le produit est maintenant favori (false s'il vient d'être retiré).
  Future<bool> toggle(int productId) async {
    final res = await _api.safeCall(() => _api.dio.post('/products/$productId/favorite'));
    return res.data['favorited'];
  }
}
