class ReferralEntry {
  final int id;
  final String name;
  final DateTime? createdAt;
  final bool rewarded;
  ReferralEntry({required this.id, required this.name, this.createdAt, required this.rewarded});

  factory ReferralEntry.fromJson(Map<String, dynamic> json) {
    return ReferralEntry(
      id: json['id'],
      name: json['name'] ?? '',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      rewarded: json['rewarded'] ?? false,
    );
  }
}

class LoyaltyTransactionEntry {
  final int id;
  final String description;
  final int points;
  final DateTime? createdAt;
  LoyaltyTransactionEntry({required this.id, required this.description, required this.points, this.createdAt});

  factory LoyaltyTransactionEntry.fromJson(Map<String, dynamic> json) {
    return LoyaltyTransactionEntry(
      id: json['id'],
      description: json['description'] ?? '',
      points: json['points'] ?? 0,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }
}

class LoyaltyData {
  final int loyaltyPoints;
  final String? referralCode;
  final String referralUrl;
  final List<ReferralEntry> referrals;
  final List<LoyaltyTransactionEntry> transactions;

  LoyaltyData({
    required this.loyaltyPoints,
    this.referralCode,
    required this.referralUrl,
    required this.referrals,
    required this.transactions,
  });

  factory LoyaltyData.fromJson(Map<String, dynamic> json) {
    return LoyaltyData(
      loyaltyPoints: json['loyalty_points'] ?? 0,
      referralCode: json['referral_code'],
      referralUrl: json['referral_url'] ?? '',
      referrals: (json['referrals'] as List? ?? []).map((e) => ReferralEntry.fromJson(e)).toList(),
      transactions: (json['transactions'] as List? ?? []).map((e) => LoyaltyTransactionEntry.fromJson(e)).toList(),
    );
  }
}
