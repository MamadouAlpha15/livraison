/// Produit tel que renvoyé par l'assistant IA — format plus léger que le
/// modèle Product du catalogue (l'assistant construit sa propre réponse).
class AssistantProduct {
  final int id;
  final String name;
  final int price;
  final String? category;
  final String shop;
  final String currency;
  final int? stock;
  final bool outOfStock;
  final String? image;

  AssistantProduct({
    required this.id,
    required this.name,
    required this.price,
    this.category,
    required this.shop,
    this.currency = 'GNF',
    this.stock,
    this.outOfStock = false,
    this.image,
  });

  factory AssistantProduct.fromJson(Map<String, dynamic> json) {
    return AssistantProduct(
      id: json['id'],
      name: json['name'] ?? '',
      price: (json['price'] as num?)?.toInt() ?? 0,
      category: json['category'],
      shop: json['shop'] ?? '',
      currency: json['currency'] ?? 'GNF',
      stock: json['stock'],
      outOfStock: json['out_of_stock'] ?? false,
      image: json['image'],
    );
  }
}

class AssistantMessage {
  final String role; // 'user' | 'assistant'
  final String content;
  final List<AssistantProduct> products;

  AssistantMessage({required this.role, required this.content, this.products = const []});

  bool get isUser => role == 'user';

  Map<String, dynamic> toHistoryJson() => {'role': role, 'content': content};
}
