import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import '../utils/guest_gate.dart';
import 'home/home_screen.dart';
import 'cart/cart_screen.dart';
import 'orders/orders_screen.dart';
import 'favorites/favorites_screen.dart';
import 'messages/conversations_screen.dart';
import 'profile/profile_screen.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => context.read<CartProvider>().refresh());
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.watch<CartProvider>().data.count;
    // En mode invité (navigation sans compte), l'accueil et la fiche produit
    // restent ouverts comme sur le site — les autres onglets demandent de se
    // connecter (panier, commandes, messagerie et profil sont derrière
    // auth+role:client côté site).
    final loggedIn = context.watch<AuthProvider>().status == AuthStatus.loggedIn;

    final screens = [
      const HomeScreen(),
      RequireAuthScreen(
        loggedIn: loggedIn,
        title: 'Favoris',
        icon: Icons.favorite_border,
        message: 'Connectez-vous pour retrouver vos produits favoris.',
        child: const FavoritesScreen(),
      ),
      RequireAuthScreen(
        loggedIn: loggedIn,
        title: 'Panier',
        icon: Icons.shopping_cart_outlined,
        message: 'Connectez-vous pour ajouter des articles à votre panier.',
        child: const CartScreen(),
      ),
      RequireAuthScreen(
        loggedIn: loggedIn,
        title: 'Commandes',
        icon: Icons.receipt_long_outlined,
        message: 'Connectez-vous pour suivre vos commandes.',
        child: const OrdersScreen(),
      ),
      RequireAuthScreen(
        loggedIn: loggedIn,
        title: 'Messages',
        icon: Icons.chat_bubble_outline,
        message: 'Connectez-vous pour discuter avec les vendeurs.',
        child: const ConversationsScreen(),
      ),
      RequireAuthScreen(
        loggedIn: loggedIn,
        title: 'Profil',
        icon: Icons.person_outline,
        message: 'Connectez-vous pour accéder à votre profil.',
        child: const ProfileScreen(),
      ),
    ];

    return Scaffold(
      body: IndexedStack(index: _index, children: screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: [
          const NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Accueil'),
          const NavigationDestination(icon: Icon(Icons.favorite_border), selectedIcon: Icon(Icons.favorite), label: 'Favoris'),
          NavigationDestination(
            icon: Badge(
              label: Text('$cartCount'),
              isLabelVisible: cartCount > 0,
              child: const Icon(Icons.shopping_cart_outlined),
            ),
            selectedIcon: const Icon(Icons.shopping_cart),
            label: 'Panier',
          ),
          const NavigationDestination(icon: Icon(Icons.receipt_long_outlined), selectedIcon: Icon(Icons.receipt_long), label: 'Commandes'),
          const NavigationDestination(icon: Icon(Icons.chat_bubble_outline), selectedIcon: Icon(Icons.chat_bubble), label: 'Messages'),
          const NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}
