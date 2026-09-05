import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/product.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../services/api_client.dart';
import '../../services/product_service.dart';
import '../../services/favorite_service.dart';
import '../../utils/formatters.dart';
import '../../utils/guest_gate.dart';
import '../order/direct_order_screen.dart';
import '../messages/chat_screen.dart';
import '../shop/shop_detail_screen.dart';

class ProductDetailScreen extends StatefulWidget {
  final int productId;
  const ProductDetailScreen({super.key, required this.productId});
  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  Product? _product;
  bool _loading = true;
  String? _error;
  int _photoIndex = 0;
  ProductVariant? _selectedVariant;
  int _quantity = 1;
  bool _favoriting = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final p = await ProductService(context.read<ApiClient>()).show(widget.productId);
      setState(() { _product = p; _loading = false; });
    } catch (e) {
      setState(() { _error = 'Impossible de charger ce produit.'; _loading = false; });
    }
  }

  Future<void> _toggleFavorite() async {
    if (_product == null || _favoriting) return;
    if (context.read<AuthProvider>().status != AuthStatus.loggedIn) {
      requireLogin(context, message: 'Connectez-vous pour ajouter ce produit à vos favoris.');
      return;
    }
    setState(() => _favoriting = true);
    try {
      final fav = await FavoriteService(context.read<ApiClient>()).toggle(_product!.id);
      setState(() => _product = Product(
            id: _product!.id, name: _product!.name, description: _product!.description,
            category: _product!.category, price: _product!.price, originalPrice: _product!.originalPrice,
            currentPrice: _product!.currentPrice, isFlashActive: _product!.isFlashActive,
            flashDiscountPercent: _product!.flashDiscountPercent, stock: _product!.stock, unit: _product!.unit,
            imageUrl: _product!.imageUrl, thumbUrl: _product!.thumbUrl, photos: _product!.photos,
            variants: _product!.variants, shop: _product!.shop, isFavorited: fav,
          ));
    } catch (_) {
    } finally {
      if (mounted) setState(() => _favoriting = false);
    }
  }

  Future<void> _addToCart() async {
    if (context.read<AuthProvider>().status != AuthStatus.loggedIn) {
      requireLogin(context, message: 'Connectez-vous pour ajouter des articles à votre panier. Vous pouvez aussi "Commander" directement sans compte.');
      return;
    }
    try {
      await context.read<CartProvider>().add(
            productId: _product!.id,
            variantId: _selectedVariant?.id,
            quantity: _quantity,
          );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ajouté au panier ✅')));
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (_error != null || _product == null) {
      return Scaffold(appBar: AppBar(), body: Center(child: Text(_error ?? 'Erreur')));
    }

    final p = _product!;
    final needsVariant = p.variants.isNotEmpty && _selectedVariant == null;
    final displayPrice = _selectedVariant?.price ?? p.currentPrice;
    final stock = _selectedVariant?.stock ?? p.stock;
    final outOfStock = stock != null && stock <= 0;

    return Scaffold(
      appBar: AppBar(
        title: Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(
            onPressed: _favoriting ? null : _toggleFavorite,
            icon: Icon(p.isFavorited ? Icons.favorite : Icons.favorite_border, color: p.isFavorited ? Colors.red : null),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AspectRatio(
              aspectRatio: 1,
              child: p.photos.isNotEmpty
                  ? CachedNetworkImage(imageUrl: p.photos[_photoIndex], fit: BoxFit.cover)
                  : Container(color: Colors.grey.shade100, child: const Icon(Icons.shopping_bag_outlined, size: 60, color: Colors.grey)),
            ),
            if (p.photos.length > 1)
              SizedBox(
                height: 56,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  itemCount: p.photos.length,
                  itemBuilder: (_, i) => GestureDetector(
                    onTap: () => setState(() => _photoIndex = i),
                    child: Container(
                      margin: const EdgeInsets.only(right: 6),
                      width: 48,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: i == _photoIndex ? const Color(0xFF6366F1) : Colors.transparent, width: 2),
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: CachedNetworkImage(imageUrl: p.photos[i], fit: BoxFit.cover),
                    ),
                  ),
                ),
              ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (p.category != null)
                    Text(p.category!.toUpperCase(), style: const TextStyle(fontSize: 10.5, color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, letterSpacing: .5)),
                  const SizedBox(height: 4),
                  Text(p.name, style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Text('${formatPrice(displayPrice)} ${p.shop?.currency ?? "GNF"}',
                          style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5))),
                      if (p.isFlashActive) ...[
                        const SizedBox(width: 8),
                        Text('${formatPrice(p.price)}', style: const TextStyle(decoration: TextDecoration.lineThrough, color: Colors.grey)),
                      ],
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (outOfStock)
                    Chip(label: const Text('Rupture de stock'), backgroundColor: Colors.red.shade50, labelStyle: TextStyle(color: Colors.red.shade700))
                  else if (stock != null && stock <= 5)
                    Chip(label: Text('$stock restant(s)'), backgroundColor: Colors.amber.shade50, labelStyle: TextStyle(color: Colors.amber.shade900)),
                  if (p.shop != null) ...[
                    const Divider(height: 32),
                    InkWell(
                      onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ShopDetailScreen(shopId: p.shop!.id))),
                      child: Row(
                        children: [
                          CircleAvatar(
                            backgroundColor: Colors.grey.shade200,
                            backgroundImage: p.shop!.imageUrl != null ? CachedNetworkImageProvider(p.shop!.imageUrl!) : null,
                            child: p.shop!.imageUrl == null ? const Icon(Icons.storefront, color: Colors.grey) : null,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Flexible(child: Text(p.shop!.name, style: const TextStyle(fontWeight: FontWeight.w700), overflow: TextOverflow.ellipsis)),
                                const Icon(Icons.chevron_right, size: 18, color: Colors.grey),
                              ],
                            ),
                          ),
                          OutlinedButton.icon(
                            onPressed: () {
                              if (context.read<AuthProvider>().status != AuthStatus.loggedIn) {
                                requireLogin(context, message: 'Connectez-vous pour discuter avec le vendeur.');
                                return;
                              }
                              Navigator.of(context).push(MaterialPageRoute(builder: (_) => ChatScreen(productId: p.id, productName: p.name)));
                            },
                            icon: const Icon(Icons.chat_bubble_outline, size: 16),
                            label: const Text('Contacter'),
                          ),
                        ],
                      ),
                    ),
                  ],
                  if (p.variants.isNotEmpty) ...[
                    const Divider(height: 32),
                    const Text('Choisissez une option', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: p.variants.map((v) {
                        final selected = _selectedVariant?.id == v.id;
                        return ChoiceChip(
                          label: Text(v.name + (v.outOfStock ? ' (épuisé)' : '')),
                          selected: selected,
                          onSelected: v.outOfStock ? null : (_) => setState(() => _selectedVariant = v),
                          selectedColor: const Color(0xFF6366F1),
                          labelStyle: TextStyle(color: selected ? Colors.white : Colors.black87),
                        );
                      }).toList(),
                    ),
                  ],
                  const Divider(height: 32),
                  Row(
                    children: [
                      const Text('Quantité', style: TextStyle(fontWeight: FontWeight.bold)),
                      const Spacer(),
                      IconButton.filledTonal(onPressed: () => setState(() => _quantity = (_quantity - 1).clamp(1, 99)), icon: const Icon(Icons.remove)),
                      SizedBox(width: 40, child: Text('$_quantity', textAlign: TextAlign.center, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold))),
                      IconButton.filledTonal(onPressed: () => setState(() => _quantity = (_quantity + 1).clamp(1, 99)), icon: const Icon(Icons.add)),
                    ],
                  ),
                  if (p.description != null && p.description!.isNotEmpty) ...[
                    const Divider(height: 32),
                    const Text('Description', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    Text(p.description!, style: TextStyle(color: Colors.grey.shade700, height: 1.4)),
                  ],
                  const SizedBox(height: 90),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
          child: Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: (outOfStock || needsVariant) ? null : _addToCart,
                  icon: const Icon(Icons.add_shopping_cart_outlined),
                  label: const Text('Ajouter'),
                  style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: (outOfStock || needsVariant)
                      ? null
                      : () => Navigator.of(context).push(MaterialPageRoute(
                            builder: (_) => DirectOrderScreen(product: p, variant: _selectedVariant, quantity: _quantity),
                          )),
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6366F1), padding: const EdgeInsets.symmetric(vertical: 14)),
                  child: const Text('Commander'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
