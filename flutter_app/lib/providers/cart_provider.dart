import 'package:flutter/foundation.dart';
import '../services/api_client.dart';
import '../services/cart_service.dart';

class CartProvider extends ChangeNotifier {
  final CartService _cartService;
  CartProvider(ApiClient api) : _cartService = CartService(api);

  CartData data = CartData.empty();
  bool loading = false;

  Future<void> refresh() async {
    loading = true;
    notifyListeners();
    try {
      data = await _cartService.get();
    } catch (_) {
      // Panier vide/non connecté : on garde l'état vide plutôt que de bloquer l'écran.
    }
    loading = false;
    notifyListeners();
  }

  Future<void> add({required int productId, int? variantId, int quantity = 1}) async {
    await _cartService.add(productId: productId, variantId: variantId, quantity: quantity);
    await refresh();
  }

  Future<void> updateQuantity(int itemId, int quantity) async {
    await _cartService.updateQuantity(itemId, quantity);
    await refresh();
  }

  Future<void> remove(int itemId) async {
    await _cartService.remove(itemId);
    await refresh();
  }

  Future<List<int>> checkout({required String deliveryDestination, required String clientPhone}) async {
    final orderIds = await _cartService.checkout(deliveryDestination: deliveryDestination, clientPhone: clientPhone);
    await refresh();
    return orderIds;
  }
}
