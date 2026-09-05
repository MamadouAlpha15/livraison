import 'shop.dart';

class ProductVariant {
  final int id;
  final String name;
  final double price;
  final int stock;
  final bool outOfStock;
  final String? imageUrl;

  ProductVariant({
    required this.id,
    required this.name,
    required this.price,
    required this.stock,
    required this.outOfStock,
    this.imageUrl,
  });

  factory ProductVariant.fromJson(Map<String, dynamic> json) {
    return ProductVariant(
      id: json['id'],
      name: json['name'] ?? '',
      price: (json['price'] as num).toDouble(),
      stock: json['stock'] ?? 0,
      outOfStock: json['out_of_stock'] ?? false,
      imageUrl: json['image_url'],
    );
  }
}

class Product {
  final int id;
  final String name;
  final String? description;
  final String? category;
  final double price;
  final double? originalPrice;
  final double currentPrice;
  final bool isFlashActive;
  final int? flashDiscountPercent;
  final int? stock;
  final String? unit;
  final String? imageUrl;
  final String? thumbUrl;
  final List<String> photos;
  final List<ProductVariant> variants;
  final ShopMini? shop;
  final bool isFavorited;

  Product({
    required this.id,
    required this.name,
    this.description,
    this.category,
    required this.price,
    this.originalPrice,
    required this.currentPrice,
    this.isFlashActive = false,
    this.flashDiscountPercent,
    this.stock,
    this.unit,
    this.imageUrl,
    this.thumbUrl,
    this.photos = const [],
    this.variants = const [],
    this.shop,
    this.isFavorited = false,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'] ?? '',
      description: json['description'],
      category: json['category'],
      price: (json['price'] as num).toDouble(),
      originalPrice: json['original_price'] != null ? (json['original_price'] as num).toDouble() : null,
      currentPrice: (json['current_price'] as num).toDouble(),
      isFlashActive: json['is_flash_active'] ?? false,
      flashDiscountPercent: json['flash_discount_percent'],
      stock: json['stock'],
      unit: json['unit'],
      imageUrl: json['image_url'],
      thumbUrl: json['thumb_url'],
      photos: (json['photos'] as List?)?.map((e) => e.toString()).toList() ?? [],
      variants: (json['variants'] as List?)?.map((e) => ProductVariant.fromJson(e)).toList() ?? [],
      shop: json['shop'] != null ? ShopMini.fromJson(json['shop']) : null,
      isFavorited: json['is_favorited'] ?? false,
    );
  }
}
