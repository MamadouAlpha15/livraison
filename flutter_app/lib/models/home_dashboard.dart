import 'product.dart';

class CategoryGroup {
  final String name;
  final List<Product> products;
  CategoryGroup({required this.name, required this.products});

  factory CategoryGroup.fromJson(Map<String, dynamic> json) {
    return CategoryGroup(
      name: json['name'] ?? '',
      products: (json['products'] as List).map((e) => Product.fromJson(e)).toList(),
    );
  }
}

class HomeStats {
  final int shopCount;
  final int productCount;
  final int deliveredCount;
  final int clientCount;
  HomeStats({this.shopCount = 0, this.productCount = 0, this.deliveredCount = 0, this.clientCount = 0});

  factory HomeStats.fromJson(Map<String, dynamic> json) {
    return HomeStats(
      shopCount: json['shop_count'] ?? 0,
      productCount: json['product_count'] ?? 0,
      deliveredCount: json['delivered_count'] ?? 0,
      clientCount: json['client_count'] ?? 0,
    );
  }
}

class HomeDashboard {
  final List<Product> flashProducts;
  final List<Product> recommendedProducts;
  final List<CategoryGroup> categoryGroups;
  final HomeStats stats;

  HomeDashboard({required this.flashProducts, required this.recommendedProducts, required this.categoryGroups, required this.stats});

  factory HomeDashboard.fromJson(Map<String, dynamic> json) {
    return HomeDashboard(
      flashProducts: (json['flash_products'] as List).map((e) => Product.fromJson(e)).toList(),
      recommendedProducts: (json['recommended_products'] as List).map((e) => Product.fromJson(e)).toList(),
      categoryGroups: (json['category_groups'] as List).map((e) => CategoryGroup.fromJson(e)).toList(),
      stats: json['stats'] != null ? HomeStats.fromJson(json['stats']) : HomeStats(),
    );
  }

  static HomeDashboard empty() => HomeDashboard(flashProducts: [], recommendedProducts: [], categoryGroups: [], stats: HomeStats());
}
