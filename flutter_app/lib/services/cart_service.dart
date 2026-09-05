import '../models/cart_item.dart';
import 'api_client.dart';

class CartData {
  final List<CartGroup> groups;
  final double grandTotal;
  final int count;
  CartData({required this.groups, required this.grandTotal, required this.count});

  factory CartData.fromJson(Map<String, dynamic> json) {
    return CartData(
      groups: (json['groups'] as List).map((e) => CartGroup.fromJson(e)).toList(),
      grandTotal: (json['grand_total'] as num).toDouble(),
      count: json['count'] ?? 0,
    );
  }

  static CartData empty() => CartData(groups: [], grandTotal: 0, count: 0);
}

class CartService {
  final ApiClient _api;
  CartService(this._api);

  Future<CartData> get() async {
    final res = await _api.safeCall(() => _api.dio.get('/cart'));
    return CartData.fromJson(res.data['data']);
  }

  Future<int> add({required int productId, int? variantId, int quantity = 1}) async {
    final res = await _api.safeCall(() => _api.dio.post('/cart/items', data: {
          'product_id': productId,
          if (variantId != null) 'variant_id': variantId,
          'quantity': quantity,
        }));
    return res.data['count'];
  }

  Future<void> updateQuantity(int itemId, int quantity) async {
    await _api.safeCall(() => _api.dio.patch('/cart/items/$itemId', data: {'quantity': quantity}));
  }

  Future<void> remove(int itemId) async {
    await _api.safeCall(() => _api.dio.delete('/cart/items/$itemId'));
  }

  /// Retourne la liste des IDs de commandes créées (une par boutique).
  Future<List<int>> checkout({required String deliveryDestination, required String clientPhone}) async {
    final res = await _api.safeCall(() => _api.dio.post('/cart/checkout', data: {
          'delivery_destination': deliveryDestination,
          'client_phone': clientPhone,
        }));
    return (res.data['order_ids'] as List).map((e) => e as int).toList();
  }
}
