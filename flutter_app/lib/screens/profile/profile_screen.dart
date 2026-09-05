import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../loyalty/loyalty_screen.dart';
import '../shop/shop_favorites_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      appBar: AppBar(title: const Text('Mon profil')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Center(
            child: CircleAvatar(
              radius: 40,
              backgroundColor: const Color(0xFF6366F1),
              child: Text(
                (user?.name.isNotEmpty == true ? user!.name[0] : '?').toUpperCase(),
                style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Center(child: Text(user?.name ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
          Center(child: Text(user?.email ?? '', style: const TextStyle(color: Colors.grey))),
          const SizedBox(height: 24),

          // ── Points fidélité & parrainage ──
          Card(
            color: const Color(0xFFEEF2FF),
            child: ListTile(
              onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const LoyaltyScreen())),
              leading: const Icon(Icons.card_giftcard, color: Color(0xFF6366F1)),
              title: const Text('Points fidélité & parrainage'),
              subtitle: Text('${user?.loyaltyPoints ?? 0} points'),
              trailing: const Icon(Icons.chevron_right),
            ),
          ),
          const SizedBox(height: 8),

          // ── Code de parrainage (copie rapide) ──
          if (user?.referralCode != null)
            Card(
              child: ListTile(
                leading: const Icon(Icons.qr_code_outlined),
                title: const Text('Mon code de parrainage'),
                subtitle: Text(user!.referralCode!, style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold)),
                trailing: IconButton(
                  icon: const Icon(Icons.copy, size: 18),
                  onPressed: () {
                    Clipboard.setData(ClipboardData(text: user.referralCode!));
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Code copié ✅')));
                  },
                ),
              ),
            ),
          const SizedBox(height: 8),

          // ── Boutiques suivies ──
          Card(
            child: ListTile(
              onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ShopFavoritesScreen())),
              leading: const Icon(Icons.storefront_outlined),
              title: const Text('Boutiques suivies'),
              trailing: const Icon(Icons.chevron_right),
            ),
          ),
          const SizedBox(height: 12),

          Card(
            child: Column(
              children: [
                ListTile(leading: const Icon(Icons.phone_outlined), title: const Text('Téléphone'), subtitle: Text(user?.phone ?? 'Non renseigné')),
                const Divider(height: 1),
                ListTile(leading: const Icon(Icons.location_on_outlined), title: const Text('Adresse'), subtitle: Text(user?.address ?? 'Non renseignée')),
              ],
            ),
          ),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            onPressed: () => context.read<AuthProvider>().logout(),
            icon: const Icon(Icons.logout, color: Colors.red),
            label: const Text('Se déconnecter', style: TextStyle(color: Colors.red)),
            style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12), side: const BorderSide(color: Colors.red)),
          ),
        ],
      ),
    );
  }
}
