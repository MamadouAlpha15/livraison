import 'package:dio/dio.dart';
import '../models/order.dart';
import 'api_client.dart';

class OrderService {
  final ApiClient _api;
  OrderService(this._api);

  Future<List<OrderSummary>> list({String status = 'all'}) async {
    final res = await _api.safeCall(() => _api.dio.get('/orders', queryParameters: {'status': status}));
    return (res.data['data'] as List).map((e) => OrderSummary.fromJson(e)).toList();
  }

  Future<OrderDetail> show(int id) async {
    final res = await _api.safeCall(() => _api.dio.get('/orders/$id'));
    return OrderDetail.fromJson(res.data['data']);
  }

  Future<int> storeDirect({
    required int productId,
    int? variantId,
    required int quantity,
    required String deliveryDestination,
    required String clientPhone,
    String? promoCode,
    int pointsToUse = 0,
    String? clientName, // requis uniquement pour une commande invité (pas de compte)
  }) async {
    final res = await _api.safeCall(() => _api.dio.post('/orders/direct', data: {
          'product_id': productId,
          if (variantId != null) 'variant_id': variantId,
          'quantity': quantity,
          'delivery_destination': deliveryDestination,
          'client_phone': clientPhone,
          if (promoCode != null && promoCode.isNotEmpty) 'promo_code': promoCode,
          if (pointsToUse > 0) 'points_to_use': pointsToUse,
          if (clientName != null && clientName.isNotEmpty) 'client_name': clientName,
        }));
    return res.data['order_id'];
  }

  Future<Map<String, dynamic>> checkPromoCode({required String code, required int shopId, required double subtotal}) async {
    final res = await _api.safeCall(() => _api.dio.post('/orders/check-promo', data: {
          'code': code,
          'shop_id': shopId,
          'subtotal': subtotal,
        }));
    return res.data;
  }

  /// Télécharge le reçu PDF (même document que Client\OrderController::downloadInvoice sur le site).
  Future<List<int>> downloadInvoice(int orderId) async {
    final res = await _api.safeCall(() => _api.dio.get(
          '/orders/$orderId/invoice',
          options: Options(responseType: ResponseType.bytes),
        ));
    return res.data as List<int>;
  }

  Future<void> submitReview(int orderId, {required int rating, String? comment}) async {
    await _api.safeCall(() => _api.dio.post('/orders/$orderId/review', data: {
          'rating': rating,
          if (comment != null && comment.isNotEmpty) 'comment': comment,
        }));
  }
}
