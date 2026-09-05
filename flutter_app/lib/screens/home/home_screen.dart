import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/product.dart';
import '../../models/home_dashboard.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/product_service.dart';
import '../../services/home_service.dart';
import '../../services/notification_service.dart';
import '../../models/notification_data.dart';
import '../../utils/formatters.dart';
import '../../utils/guest_gate.dart';
import '../../widgets/product_card.dart';
import '../product/product_detail_screen.dart';
import '../assistant/assistant_screen.dart';
import '../notifications/notifications_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late final ProductService _productService = ProductService(context.read<ApiClient>());
  late final HomeService _homeService = HomeService(context.read<ApiClient>());
  late final NotificationService _notificationService = NotificationService(context.read<ApiClient>());
  final _searchCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  HomeDashboard _dashboard = HomeDashboard.empty();
  NotificationFeed _notifications = NotificationFeed.empty();
  List<Product> _products = [];
  List<String> _categories = [];
  String? _selectedCategory;
  int _page = 1;
  bool _hasMore = true;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  bool get _isBrowsing => _searchCtrl.text.trim().isNotEmpty || _selectedCategory != null;

  @override
  void initState() {
    super.initState();
    _scrollCtrl.addListener(() {
      if (_scrollCtrl.position.pixels > _scrollCtrl.position.maxScrollExtent - 300) _loadMore();
    });
    _loadAll();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    if (context.read<AuthProvider>().status != AuthStatus.loggedIn) return; // invité : pas de notifications personnelles
    try {
      final feed = await _notificationService.feed();
      if (mounted) setState(() => _notifications = feed);
    } catch (_) {}
  }

  Future<void> _loadAll() async {
    setState(() { _loading = true; _error = null; });
    try {
      final results = await Future.wait([
        _homeService.dashboard(),
        _productService.categories(),
        _productService.list(page: 1),
      ]);
      setState(() {
        _dashboard = results[0] as HomeDashboard;
        _categories = results[1] as List<String>;
        final page = results[2] as ProductPage;
        _products = page.items;
        _page = page.currentPage;
        _hasMore = page.hasMore;
      });
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadProducts({bool reset = false}) async {
    if (reset) setState(() => _loading = true);
    try {
      final result = await _productService.list(search: _searchCtrl.text.trim(), category: _selectedCategory, page: 1);
      setState(() { _products = result.items; _page = 1; _hasMore = result.hasMore; });
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMore) return;
    setState(() => _loadingMore = true);
    try {
      final result = await _productService.list(search: _searchCtrl.text.trim(), category: _selectedCategory, page: _page + 1);
      setState(() { _products.addAll(result.items); _page = result.currentPage; _hasMore = result.hasMore; });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  void _openProduct(int id) => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ProductDetailScreen(productId: id)));

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final loggedIn = auth.status == AuthStatus.loggedIn;
    final userName = auth.user?.name.split(' ').first;
    final greeting = userName != null ? 'Bonjour, $userName 👋' : 'Bienvenue sur Shopio 👋';

    return Scaffold(
      body: SafeArea(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : RefreshIndicator(
                onRefresh: _loadAll,
                child: CustomScrollView(
                  controller: _scrollCtrl,
                  slivers: [
                    // ── En-tête (hero) ──
                    SliverToBoxAdapter(
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.fromLTRB(18, 18, 18, 16),
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF6366F1)], begin: Alignment.topLeft, end: Alignment.bottomRight),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(greeting, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
                                ),
                                Stack(
                                  clipBehavior: Clip.none,
                                  children: [
                                    IconButton(
                                      onPressed: () async {
                                        if (!loggedIn) {
                                          requireLogin(context, message: 'Connectez-vous pour voir vos notifications.');
                                          return;
                                        }
                                        await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                                        _loadNotifications();
                                      },
                                      icon: const Icon(Icons.notifications_outlined, color: Colors.white),
                                    ),
                                    if (_notifications.totalBadge > 0)
                                      Positioned(
                                        right: 6, top: 6,
                                        child: Container(
                                          padding: const EdgeInsets.all(3),
                                          decoration: const BoxDecoration(color: Colors.redAccent, shape: BoxShape.circle),
                                          constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                                          child: Text('${_notifications.totalBadge}', textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                                        ),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            const Text('Trouvez ce qu\'il vous faut, livré chez vous.', style: TextStyle(color: Colors.white70, fontSize: 12.5)),
                            const SizedBox(height: 14),
                            TextField(
                              controller: _searchCtrl,
                              onSubmitted: (_) => _loadProducts(reset: true),
                              decoration: InputDecoration(
                                hintText: 'Rechercher un produit...',
                                prefixIcon: const Icon(Icons.search),
                                filled: true,
                                fillColor: Colors.white,
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // ── Statistiques de confiance ──
                    SliverToBoxAdapter(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                        child: Row(
                          children: [
                            _StatItem(icon: Icons.storefront_outlined, value: _dashboard.stats.shopCount, label: 'Boutiques'),
                            _StatItem(icon: Icons.shopping_bag_outlined, value: _dashboard.stats.productCount, label: 'Produits'),
                            _StatItem(icon: Icons.local_shipping_outlined, value: _dashboard.stats.deliveredCount, label: 'Livraisons'),
                            _StatItem(icon: Icons.people_outline, value: _dashboard.stats.clientCount, label: 'Clients'),
                          ],
                        ),
                      ),
                    ),

                    if (_categories.isNotEmpty)
                      SliverToBoxAdapter(
                        child: SizedBox(
                          height: 44,
                          child: ListView(
                            scrollDirection: Axis.horizontal,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            children: [
                              _CategoryChip(label: 'Tout', selected: _selectedCategory == null, onTap: () {
                                setState(() => _selectedCategory = null);
                                _loadProducts(reset: true);
                              }),
                              const SizedBox(width: 6),
                              ..._categories.map((c) => Padding(
                                    padding: const EdgeInsets.only(right: 6),
                                    child: _CategoryChip(label: c, selected: _selectedCategory == c, onTap: () {
                                      setState(() => _selectedCategory = c);
                                      _loadProducts(reset: true);
                                    }),
                                  )),
                            ],
                          ),
                        ),
                      ),

                    // ── Ventes flash + Populaire par catégorie (masqué pendant une recherche/filtre) ──
                    if (!_isBrowsing) ...[
                      if (_dashboard.flashProducts.isNotEmpty)
                        SliverToBoxAdapter(
                          child: _HorizontalSection(
                            title: '⚡ Ventes Flash',
                            subtitle: 'Offres à durée limitée',
                            titleColor: Colors.red,
                            products: _dashboard.flashProducts,
                            onTapProduct: _openProduct,
                            showFlashPrice: true,
                          ),
                        ),
                      if (_dashboard.recommendedProducts.isNotEmpty)
                        SliverToBoxAdapter(
                          child: _HorizontalSection(
                            title: '✨ Recommandé pour vous',
                            products: _dashboard.recommendedProducts,
                            onTapProduct: _openProduct,
                          ),
                        ),
                      ..._dashboard.categoryGroups.map((g) => SliverToBoxAdapter(
                            child: _HorizontalSection(
                              title: 'Populaire en ${g.name}',
                              products: g.products,
                              onTapProduct: _openProduct,
                            ),
                          )),
                      const SliverToBoxAdapter(
                        child: Padding(
                          padding: EdgeInsets.fromLTRB(16, 12, 16, 6),
                          child: Text('Tous les produits', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],

                    if (_error != null)
                      SliverFillRemaining(child: Center(child: Text(_error!)))
                    else if (_products.isEmpty)
                      const SliverFillRemaining(child: Center(child: Text('Aucun produit trouvé.')))
                    else
                      SliverPadding(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        sliver: SliverGrid(
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2, mainAxisSpacing: 10, crossAxisSpacing: 10, childAspectRatio: 0.62,
                          ),
                          delegate: SliverChildBuilderDelegate(
                            (context, i) => ProductCard(product: _products[i], onTap: () => _openProduct(_products[i].id)),
                            childCount: _products.length,
                          ),
                        ),
                      ),
                    if (_loadingMore)
                      const SliverToBoxAdapter(child: Padding(padding: EdgeInsets.all(16), child: Center(child: CircularProgressIndicator()))),
                    const SliverToBoxAdapter(child: SizedBox(height: 80)),
                  ],
                ),
              ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AssistantScreen())),
        backgroundColor: const Color(0xFF6366F1),
        icon: const Icon(Icons.auto_awesome, color: Colors.white),
        label: const Text('Assistant', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}

class _CategoryChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;
  const _CategoryChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onTap(),
      selectedColor: const Color(0xFF6366F1),
      labelStyle: TextStyle(color: selected ? Colors.white : Colors.black87, fontSize: 12.5),
    );
  }
}

/// Une rangée horizontale défilante de produits (ventes flash, populaire par
/// catégorie, recommandations) — même principe que les sections du site.
class _HorizontalSection extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Color? titleColor;
  final List<Product> products;
  final void Function(int) onTapProduct;
  final bool showFlashPrice;

  const _HorizontalSection({
    required this.title,
    this.subtitle,
    this.titleColor,
    required this.products,
    required this.onTapProduct,
    this.showFlashPrice = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(title, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: titleColor)),
                if (subtitle != null) ...[
                  const SizedBox(width: 8),
                  Text(subtitle!, style: const TextStyle(fontSize: 11, color: Colors.grey)),
                ],
              ],
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 168,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: products.length,
              itemBuilder: (_, i) {
                final p = products[i];
                return GestureDetector(
                  onTap: () => onTapProduct(p.id),
                  child: Container(
                    width: 128,
                    margin: const EdgeInsets.only(right: 10),
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey.shade200),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SizedBox(
                          height: 96,
                          width: double.infinity,
                          child: Stack(
                            fit: StackFit.expand,
                            children: [
                              p.imageUrl != null
                                  ? CachedNetworkImage(imageUrl: p.imageUrl!, fit: BoxFit.cover)
                                  : Container(color: Colors.grey.shade100, child: const Icon(Icons.shopping_bag_outlined, color: Colors.grey)),
                              if (showFlashPrice && p.isFlashActive)
                                Positioned(
                                  top: 4, left: 4,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                    decoration: BoxDecoration(color: Colors.red.shade600, borderRadius: BorderRadius.circular(20)),
                                    child: Text('-${p.flashDiscountPercent ?? 0}%', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.all(6),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
                              Text('${formatPrice(p.currentPrice)} ${p.shop?.currency ?? "GNF"}', style: const TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final IconData icon;
  final int value;
  final String label;
  const _StatItem({required this.icon, required this.value, required this.label});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Icon(icon, color: const Color(0xFF6366F1), size: 20),
          const SizedBox(height: 4),
          Text('$value+', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
          Text(label, style: const TextStyle(fontSize: 10, color: Colors.grey), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}
