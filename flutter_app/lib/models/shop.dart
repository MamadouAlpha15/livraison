class Shop {
  final int id;
  final String name;
  final String? type;
  final String? description;
  final String? address;
  final String? phone;
  final String? country;
  final String currency;
  final String? imageUrl;

  Shop({
    required this.id,
    required this.name,
    this.type,
    this.description,
    this.address,
    this.phone,
    this.country,
    this.currency = 'GNF',
    this.imageUrl,
  });

  factory Shop.fromJson(Map<String, dynamic> json) {
    return Shop(
      id: json['id'],
      name: json['name'] ?? '',
      type: json['type'],
      description: json['description'],
      address: json['address'],
      phone: json['phone'],
      country: json['country'],
      currency: json['currency'] ?? 'GNF',
      imageUrl: json['image_url'],
    );
  }
}

/// Fiche boutique complète (GET /shops/{id}) : ajoute les signaux de confiance
/// affichés sur la page publique du site (note moyenne, avis, livraisons).
class ShopDetail extends Shop {
  final double? reviewAvg;
  final int reviewCount;
  final int deliveredCount;

  ShopDetail({
    required super.id,
    required super.name,
    super.type,
    super.description,
    super.address,
    super.phone,
    super.country,
    super.currency,
    super.imageUrl,
    this.reviewAvg,
    this.reviewCount = 0,
    this.deliveredCount = 0,
  });

  factory ShopDetail.fromJson(Map<String, dynamic> json) {
    return ShopDetail(
      id: json['id'],
      name: json['name'] ?? '',
      type: json['type'],
      description: json['description'],
      address: json['address'],
      phone: json['phone'],
      country: json['country'],
      currency: json['currency'] ?? 'GNF',
      imageUrl: json['image_url'],
      reviewAvg: (json['review_avg'] as num?)?.toDouble(),
      reviewCount: json['review_count'] ?? 0,
      deliveredCount: json['delivered_count'] ?? 0,
    );
  }
}

class ShopReview {
  final int id;
  final int rating;
  final String? comment;
  final String clientName;
  final DateTime? createdAt;

  ShopReview({required this.id, required this.rating, this.comment, required this.clientName, this.createdAt});

  factory ShopReview.fromJson(Map<String, dynamic> json) {
    return ShopReview(
      id: json['id'],
      rating: json['rating'] ?? 0,
      comment: json['comment'],
      clientName: json['client_name'] ?? 'Client',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }
}

/// Boutique suivie ("favorite") — GET /shop-favorites.
class FollowedShop {
  final int id;
  final String name;
  final String? type;
  final String? imageUrl;
  final String? country;
  final int productsCount;
  final int salesCount;

  FollowedShop({
    required this.id,
    required this.name,
    this.type,
    this.imageUrl,
    this.country,
    this.productsCount = 0,
    this.salesCount = 0,
  });

  factory FollowedShop.fromJson(Map<String, dynamic> json) {
    return FollowedShop(
      id: json['id'],
      name: json['name'] ?? '',
      type: json['type'],
      imageUrl: json['image_url'],
      country: json['country'],
      productsCount: json['products_count'] ?? 0,
      salesCount: json['sales_count'] ?? 0,
    );
  }
}

/// Version allégée intégrée dans les réponses produit/commande (pas tous les champs).
class ShopMini {
  final int id;
  final String name;
  final String? imageUrl;
  final String currency;

  ShopMini({required this.id, required this.name, this.imageUrl, this.currency = 'GNF'});

  factory ShopMini.fromJson(Map<String, dynamic> json) {
    return ShopMini(
      id: json['id'],
      name: json['name'] ?? '',
      imageUrl: json['image_url'],
      currency: json['currency'] ?? 'GNF',
    );
  }
}
