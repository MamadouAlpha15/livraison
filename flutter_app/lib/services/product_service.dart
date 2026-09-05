import '../models/product.dart';
import '../models/shop.dart';
import 'api_client.dart';

class ProductPage {
  final List<Product> items;
  final int currentPage;
  final int lastPage;
  ProductPage({required this.items, required this.currentPage, required this.lastPage});
  bool get hasMore => currentPage < lastPage;
}

class ProductService {
  final ApiClient _api;
  ProductService(this._api);

  Future<ProductPage> list({String? search, String? category, int page = 1}) async {
    final res = await _api.safeCall(() => _api.dio.get('/products', queryParameters: {
          if (search != null && search.isNotEmpty) 's': search,
          if (category != null && category.isNotEmpty) 'cat': category,
          'page': page,
        }));
    return ProductPage(
      items: (res.data['data'] as List).map((e) => Product.fromJson(e)).toList(),
      currentPage: res.data['meta']['current_page'],
      lastPage: res.data['meta']['last_page'],
    );
  }

  Future<Product> show(int id) async {
    final res = await _api.safeCall(() => _api.dio.get('/products/$id'));
    return Product.fromJson(res.data['data']);
  }

  Future<List<String>> categories() async {
    final res = await _api.safeCall(() => _api.dio.get('/categories'));
    return (res.data['data'] as List).map((e) => e.toString()).toList();
  }

  Future<List<Shop>> shops({String? search, int page = 1}) async {
    final res = await _api.safeCall(() => _api.dio.get('/shops', queryParameters: {
          if (search != null && search.isNotEmpty) 's': search,
          'page': page,
        }));
    return (res.data['data'] as List).map((e) => Shop.fromJson(e)).toList();
  }
}
