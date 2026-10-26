import 'package:flutter/material.dart';

/// Badge discret « Sponsorisé » des produits et boutiques mis en avant par une campagne payée.
class SponsoredTag extends StatelessWidget {
  const SponsoredTag({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(color: const Color(0xFFEEF1F6), borderRadius: BorderRadius.circular(20)),
      child: const Text(
        'Sponsorisé',
        style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: Color(0xFF62627C)),
      ),
    );
  }
}
