import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/product.dart';
import '../../models/shop.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/shop_service.dart';
import '../../services/shop_favorite_service.dart';
import '../../utils/formatters.dart';
import '../../utils/guest_gate.dart';
import '../../widgets/product_card.dart';
import '../product/product_detail_screen.dart';

/// Fiche boutique publique (GET /shops/{id}) : reprend PublicShopController —
/// note moyenne, avis, nb de livraisons, catalogue de la boutique, et bouton
/// "Suivre" (Api\V1\ShopFavoriteController, distinct des favoris produit).
class ShopDetailScreen extends StatefulWidget {
  final int shopId;
  const ShopDetailScreen({super.key, required this.shopId});
  @override
  State<ShopDetailScreen> createState() => _ShopDetailScreenState();
}

class _ShopDetailScreenState extends State<ShopDetailScreen> with SingleTickerProviderStateMixin {
  late final ShopService _service = ShopService(context.read<ApiClient>());
  ShopDetail? _shop;
  List<Product> _products = [];
  List<ShopReview> _reviews = [];
  bool _loading = true;
  bool _following = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final shop = await _service.show(widget.shopId);
      final products = await _service.products(widget.shopId);
      final reviews = await _service.reviews(widget.shopId);
      setState(() {
        _shop = shop;
        _products = products.items;
        _reviews = reviews;
      });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _toggleFollow() async {
    if (context.read<AuthProvider>().status != AuthStatus.loggedIn) {
      requireLogin(context, message: 'Connectez-vous pour suivre cette boutique.');
      return;
    }
    setState(() => _following = true);
    try {
      await ShopFavoriteService(context.read<ApiClient>()).toggle(widget.shopId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Préférence mise à jour ✅')));
    } catch (_) {
    } finally {
      if (mounted) setState(() => _following = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_shop == null) return Scaffold(appBar: AppBar(), body: const Center(child: Text('Boutique introuvable.')));

    final s = _shop!;
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        body: NestedScrollView(
          headerSliverBuilder: (context, _) => [
            SliverAppBar(
              pinned: true,
              expandedHeight: 190,
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  decoration: const BoxDecoration(gradient: LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF6366F1)], begin: Alignment.topLeft, end: Alignment.bottomRight)),
                  child: SafeArea(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(16, 50, 16, 16),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          CircleAvatar(
                            radius: 30,
                            backgroundColor: Colors.white,
                            backgroundImage: s.imageUrl != null ? CachedNetworkImageProvider(s.imageUrl!) : null,
                            child: s.imageUrl == null ? const Icon(Icons.storefront, color: Color(0xFF6366F1), size: 28) : null,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(s.name, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900), maxLines: 1, overflow: TextOverflow.ellipsis),
                                if (s.type != null) Text(s.type!, style: const TextStyle(color: Colors.white70, fontSize: 12)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
              actions: [
                IconButton(
                  onPressed: _following ? null : _toggleFollow,
                  icon: const Icon(Icons.favorite_border, color: Colors.white),
                  tooltip: 'Suivre cette boutique',
                ),
              ],
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    _TrustStat(icon: Icons.star, value: s.reviewAvg != null ? s.reviewAvg!.toStringAsFixed(1) : '—', label: '${s.reviewCount} avis'),
                    _TrustStat(icon: Icons.local_shipping_outlined, value: '${s.deliveredCount}', label: 'Livraisons'),
                    if (s.address != null) _TrustStat(icon: Icons.location_on_outlined, value: '', label: s.address!),
                  ],
                ),
              ),
            ),
            if (s.description != null && s.description!.isNotEmpty)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                  child: Text(s.description!, style: TextStyle(color: Colors.grey.shade700)),
                ),
              ),
            const SliverToBoxAdapter(
              child: TabBar(
                labelColor: Color(0xFF6366F1),
                unselectedLabelColor: Colors.grey,
                indicatorColor: Color(0xFF6366F1),
                tabs: [Tab(text: 'Produits'), Tab(text: 'Avis')],
              ),
            ),
          ],
          body: TabBarView(
            children: [
              _products.isEmpty
                  ? const Center(child: Text('Aucun produit pour l\'instant.'))
                  : GridView.builder(
                      padding: const EdgeInsets.all(10),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, mainAxisSpacing: 10, crossAxisSpacing: 10, childAspectRatio: 0.62),
                      itemCount: _products.length,
                      itemBuilder: (_, i) => ProductCard(
                        product: _products[i],
                        onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ProductDetailScreen(productId: _products[i].id))),
                      ),
                    ),
              _reviews.isEmpty
                  ? const Center(child: Text('Aucun avis pour l\'instant.'))
                  : ListView.separated(
                      padding: const EdgeInsets.all(16),
                      itemCount: _reviews.length,
                      separatorBuilder: (_, _) => const Divider(height: 24),
                      itemBuilder: (_, i) {
                        final r = _reviews[i];
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(r.clientName, style: const TextStyle(fontWeight: FontWeight.bold)),
                                const Spacer(),
                                Row(children: List.generate(5, (j) => Icon(j < r.rating ? Icons.star : Icons.star_border, size: 14, color: Colors.amber))),
                              ],
                            ),
                            if (r.createdAt != null) Text(formatDateTime(r.createdAt!), style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            if (r.comment != null && r.comment!.isNotEmpty) ...[
                              const SizedBox(height: 6),
                              Text(r.comment!),
                            ],
                          ],
                        );
                      },
                    ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TrustStat extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  const _TrustStat({required this.icon, required this.value, required this.label});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Icon(icon, color: const Color(0xFF6366F1), size: 18),
          const SizedBox(height: 4),
          if (value.isNotEmpty) Text(value, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
          Text(label, style: const TextStyle(fontSize: 10, color: Colors.grey), textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }
}
