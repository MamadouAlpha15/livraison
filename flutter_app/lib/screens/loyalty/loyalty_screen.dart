import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../models/loyalty.dart';
import '../../services/api_client.dart';
import '../../services/loyalty_service.dart';
import '../../utils/formatters.dart';
import '../../utils/guest_gate.dart';

/// Reprend Client\LoyaltyController@index (route /client/fidelite) : solde de
/// points, lien de parrainage, filleuls, historique des transactions.
class LoyaltyScreen extends StatefulWidget {
  const LoyaltyScreen({super.key});
  @override
  State<LoyaltyScreen> createState() => _LoyaltyScreenState();
}

class _LoyaltyScreenState extends State<LoyaltyScreen> {
  late final LoyaltyService _service = LoyaltyService(context.read<ApiClient>());
  LoyaltyData? _data;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final data = await _service.get();
      setState(() => _data = data);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _copyLink() {
    if (_data == null) return;
    Clipboard.setData(ClipboardData(text: _data!.referralUrl));
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lien copié ✅')));
  }

  Future<void> _shareOnWhatsApp() async {
    if (_data == null) return;
    final text = Uri.encodeComponent('Rejoins-moi sur Shopio et profite de bons plans ! ${_data!.referralUrl}');
    await openExternalUrl(context, 'https://wa.me/?text=$text');
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    final d = _data;
    if (d == null) {
      return Scaffold(appBar: AppBar(title: const Text('Mes points & Parrainage')), body: const Center(child: Text('Impossible de charger vos points.')));
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Mes points & Parrainage')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // ── Hero solde ──
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(colors: [Color(0xFF131921), Color(0xFF232F3E)]),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('🎁 MON SOLDE DE POINTS FIDÉLITÉ', style: TextStyle(color: Colors.white54, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: .5)),
                        const SizedBox(height: 6),
                        Text('${formatPrice(d.loyaltyPoints)} points', style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w900)),
                        const SizedBox(height: 4),
                        const Text('1 point = 1 GNF de réduction sur votre prochaine commande', style: TextStyle(color: Colors.white54, fontSize: 11.5)),
                      ],
                    ),
                  ),
                  const Text('💎', style: TextStyle(fontSize: 44)),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // ── Comment gagner des points ──
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('✨ Comment gagner des points', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                    const SizedBox(height: 14),
                    _HowRow(icon: '🛒', title: '0,1% de cashback', subtitle: 'sur chaque commande livrée'),
                    const SizedBox(height: 10),
                    _HowRow(icon: '👥', title: '2 000 points', subtitle: 'pour chaque ami parrainé qui commande'),
                    const SizedBox(height: 10),
                    _HowRow(icon: '🏷️', title: 'Utilisez vos points', subtitle: 'jusqu\'à 50% de réduction à la commande'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // ── Parrainage ──
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('🔗 Parrainez vos amis', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                    const SizedBox(height: 10),
                    Text.rich(
                      TextSpan(
                        style: TextStyle(fontSize: 12.5, color: Colors.grey.shade700, height: 1.5),
                        children: const [
                          TextSpan(text: 'Partagez votre lien : dès que votre filleul reçoit sa première commande, vous gagnez '),
                          TextSpan(text: '2 000 points', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF067D62))),
                          TextSpan(text: ' et lui reçoit '),
                          TextSpan(text: '1 000 points', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF067D62))),
                          TextSpan(text: ' de bienvenue.'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                      child: Row(
                        children: [
                          Expanded(child: Text(d.referralUrl, style: const TextStyle(fontFamily: 'monospace', fontSize: 12), overflow: TextOverflow.ellipsis)),
                          IconButton(onPressed: _copyLink, icon: const Icon(Icons.copy, size: 18)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _shareOnWhatsApp,
                        style: FilledButton.styleFrom(backgroundColor: const Color(0xFF25D366)),
                        icon: const Icon(Icons.chat, size: 18),
                        label: const Text('Partager sur WhatsApp'),
                      ),
                    ),
                    if (d.referrals.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      Text('VOS FILLEULS (${d.referrals.length})', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey.shade600, letterSpacing: .5)),
                      const SizedBox(height: 8),
                      ...d.referrals.map((r) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(r.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      if (r.createdAt != null) Text('Inscrit le ${formatDateTime(r.createdAt!).split(' à').first}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: r.rewarded ? Colors.green.shade50 : Colors.amber.shade50,
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    r.rewarded ? '✓ Récompensé' : '⏳ En attente',
                                    style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: r.rewarded ? Colors.green.shade800 : Colors.amber.shade900),
                                  ),
                                ),
                              ],
                            ),
                          )),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // ── Historique ──
            Card(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('📜 Historique de mes points', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                  ),
                  if (d.transactions.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(24),
                      child: Center(child: Text('Aucun mouvement pour le moment.', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ...d.transactions.map((t) => ListTile(
                          title: Text(t.description, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                          subtitle: t.createdAt != null ? Text(formatDateTime(t.createdAt!), style: const TextStyle(fontSize: 11)) : null,
                          trailing: Text(
                            '${t.points >= 0 ? '+' : ''}${formatPrice(t.points)}',
                            style: TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.w900, color: t.points >= 0 ? Colors.green.shade700 : Colors.red.shade700),
                          ),
                        )),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HowRow extends StatelessWidget {
  final String icon;
  final String title;
  final String subtitle;
  const _HowRow({required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(icon, style: const TextStyle(fontSize: 20)),
        const SizedBox(width: 10),
        Expanded(
          child: RichText(
            text: TextSpan(
              style: TextStyle(fontSize: 12.5, color: Colors.grey.shade800, height: 1.4),
              children: [
                TextSpan(text: '$title\n', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black)),
                TextSpan(text: subtitle),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
