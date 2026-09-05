import '../models/product.dart';
import '../models/shop.dart';
import 'api_client.dart';
import 'product_service.dart';

class ShopService {
  final ApiClient _api;
  ShopService(this._api);

  Future<ShopDetail> show(int shopId) async {
    final res = await _api.safeCall(() => _api.dio.get('/shops/$shopId'));
    return ShopDetail.fromJson(res.data['data']);
  }

  Future<ProductPage> products(int shopId, {String? category, int page = 1}) async {
    final res = await _api.safeCall(() => _api.dio.get('/shops/$shopId/products', queryParameters: {
          if (category != null && category.isNotEmpty) 'cat': category,
          'page': page,
        }));
    return ProductPage(
      items: (res.data['data'] as List).map((e) => Product.fromJson(e)).toList(),
      currentPage: res.data['meta']['current_page'],
      lastPage: res.data['meta']['last_page'],
    );
  }

  Future<List<ShopReview>> reviews(int shopId, {int page = 1}) async {
    final res = await _api.safeCall(() => _api.dio.get('/shops/$shopId/reviews', queryParameters: {'page': page}));
    return (res.data['data'] as List).map((e) => ShopReview.fromJson(e)).toList();
  }
}
