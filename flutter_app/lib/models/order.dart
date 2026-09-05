import 'shop.dart';

class OrderItem {
  final int id;
  final int productId;
  final String productName;
  final String? variantName;
  final String? imageUrl;
  final double price;
  final int quantity;
  final double subtotal;

  OrderItem({
    required this.id,
    required this.productId,
    required this.productName,
    this.variantName,
    this.imageUrl,
    required this.price,
    required this.quantity,
    required this.subtotal,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productId: json['product_id'],
      productName: json['product_name'] ?? '',
      variantName: json['variant_name'],
      imageUrl: json['image_url'],
      price: (json['price'] as num).toDouble(),
      quantity: json['quantity'],
      subtotal: (json['subtotal'] as num).toDouble(),
    );
  }
}

/// Version allégée pour la liste "Mes commandes".
class OrderSummary {
  final int id;
  final String status;
  final double total;
  final int shopId;
  final String shopName;
  final int itemsCount;
  final DateTime? createdAt;
  final DateTime? deliveredAt;

  OrderSummary({
    required this.id,
    required this.status,
    required this.total,
    required this.shopId,
    required this.shopName,
    required this.itemsCount,
    this.createdAt,
    this.deliveredAt,
  });

  factory OrderSummary.fromJson(Map<String, dynamic> json) {
    return OrderSummary(
      id: json['id'],
      status: json['status'] ?? 'en_attente',
      total: (json['total'] as num).toDouble(),
      shopId: json['shop']['id'],
      shopName: json['shop']['name'] ?? '',
      itemsCount: json['items_count'] ?? 0,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      deliveredAt: json['delivered_at'] != null ? DateTime.tryParse(json['delivered_at']) : null,
    );
  }
}

class OrderTracking {
  final double lat;
  final double lng;
  final DateTime? lastPingAt;

  OrderTracking({required this.lat, required this.lng, this.lastPingAt});

  factory OrderTracking.fromJson(Map<String, dynamic> json) {
    return OrderTracking(
      lat: (json['lat'] as num).toDouble(),
      lng: (json['lng'] as num).toDouble(),
      lastPingAt: json['last_ping_at'] != null ? DateTime.tryParse(json['last_ping_at']) : null,
    );
  }
}

/// Détail complet d'une commande (écran de suivi).
class OrderDetail {
  final int id;
  final String status;
  final double total;
  final String? deliveryDestination;
  final String? clientPhone;
  final DateTime? createdAt;
  final DateTime? deliveredAt;
  final Shop shop;
  final List<OrderItem> items;
  final String? paymentMethod;
  final String? paymentStatus;
  final String? livreurName;
  final String? livreurPhone;
  final OrderTracking? tracking;
  final String? deliveryProofPhotoUrl;

  OrderDetail({
    required this.id,
    required this.status,
    required this.total,
    this.deliveryDestination,
    this.clientPhone,
    this.createdAt,
    this.deliveredAt,
    required this.shop,
    required this.items,
    this.paymentMethod,
    this.paymentStatus,
    this.livreurName,
    this.livreurPhone,
    this.tracking,
    this.deliveryProofPhotoUrl,
  });

  factory OrderDetail.fromJson(Map<String, dynamic> json) {
    return OrderDetail(
      id: json['id'],
      status: json['status'] ?? 'en_attente',
      total: (json['total'] as num).toDouble(),
      deliveryDestination: json['delivery_destination'],
      clientPhone: json['client_phone'],
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      deliveredAt: json['delivered_at'] != null ? DateTime.tryParse(json['delivered_at']) : null,
      shop: Shop.fromJson(json['shop']),
      items: (json['items'] as List).map((e) => OrderItem.fromJson(e)).toList(),
      paymentMethod: json['payment_method'],
      paymentStatus: json['payment_status'],
      livreurName: json['livreur']?['name'],
      livreurPhone: json['livreur']?['phone'],
      tracking: json['tracking'] != null ? OrderTracking.fromJson(json['tracking']) : null,
      deliveryProofPhotoUrl: json['delivery_proof_photo_url'],
    );
  }
}

/// Libellés/couleurs des statuts pour l'affichage — même vocabulaire que le site.
class OrderStatus {
  static String label(String status) {
    switch (status) {
      case 'en_attente':
        return 'En attente';
      case 'confirmée':
        return 'Confirmée';
      case 'en_livraison':
        return 'En livraison';
      case 'livrée':
        return 'Livrée';
      case 'annulée':
        return 'Annulée';
      default:
        return status;
    }
  }
}
