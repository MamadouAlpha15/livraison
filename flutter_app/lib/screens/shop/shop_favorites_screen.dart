import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/shop.dart';
import '../../services/api_client.dart';
import '../../services/shop_favorite_service.dart';
import 'shop_detail_screen.dart';

/// Reprend Client\FavoriteController::index — les boutiques suivies par le client.
class ShopFavoritesScreen extends StatefulWidget {
  const ShopFavoritesScreen({super.key});
  @override
  State<ShopFavoritesScreen> createState() => _ShopFavoritesScreenState();
}

class _ShopFavoritesScreenState extends State<ShopFavoritesScreen> {
  late final ShopFavoriteService _service = ShopFavoriteService(context.read<ApiClient>());
  List<FollowedShop> _shops = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final shops = await _service.list();
      setState(() => _shops = shops);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Boutiques suivies')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _shops.isEmpty
              ? const Center(child: Text('Vous ne suivez aucune boutique pour l\'instant.'))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(12),
                    itemCount: _shops.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (_, i) {
                      final s = _shops[i];
                      return Card(
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: Colors.grey.shade200,
                            backgroundImage: s.imageUrl != null ? CachedNetworkImageProvider(s.imageUrl!) : null,
                            child: s.imageUrl == null ? const Icon(Icons.storefront, color: Colors.grey) : null,
                          ),
                          title: Text(s.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('${s.type ?? ''} • ${s.productsCount} produit(s) • ${s.salesCount} vente(s)'),
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () async {
                            await Navigator.of(context).push(MaterialPageRoute(builder: (_) => ShopDetailScreen(shopId: s.id)));
                            _load();
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
