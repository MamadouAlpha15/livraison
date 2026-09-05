class CartItem {
  final int id;
  final int productId;
  final int? variantId;
  final String productName;
  final String? variantName;
  final String? imageUrl;
  final double unitPrice;
  final int quantity;
  final double subtotal;
  final int? availableStock;
  final int shopId;
  final String shopName;

  CartItem({
    required this.id,
    required this.productId,
    this.variantId,
    required this.productName,
    this.variantName,
    this.imageUrl,
    required this.unitPrice,
    required this.quantity,
    required this.subtotal,
    this.availableStock,
    required this.shopId,
    required this.shopName,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      id: json['id'],
      productId: json['product_id'],
      variantId: json['variant_id'],
      productName: json['product_name'] ?? '',
      variantName: json['variant_name'],
      imageUrl: json['image_url'],
      unitPrice: (json['unit_price'] as num).toDouble(),
      quantity: json['quantity'],
      subtotal: (json['subtotal'] as num).toDouble(),
      availableStock: json['available_stock'],
      shopId: json['shop']['id'],
      shopName: json['shop']['name'] ?? '',
    );
  }
}

class CartGroup {
  final int shopId;
  final String shopName;
  final List<CartItem> items;
  final double subtotal;

  CartGroup({required this.shopId, required this.shopName, required this.items, required this.subtotal});

  factory CartGroup.fromJson(Map<String, dynamic> json) {
    return CartGroup(
      shopId: json['shop']['id'],
      shopName: json['shop']['name'] ?? '',
      items: (json['items'] as List).map((e) => CartItem.fromJson(e)).toList(),
      subtotal: (json['subtotal'] as num).toDouble(),
    );
  }
}
