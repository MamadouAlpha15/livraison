class AppUser {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? address;
  final String? country;
  final int loyaltyPoints;
  final String? referralCode;

  AppUser({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.address,
    this.country,
    this.loyaltyPoints = 0,
    this.referralCode,
  });

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      address: json['address'],
      country: json['country'],
      loyaltyPoints: json['loyalty_points'] ?? 0,
      referralCode: json['referral_code'],
    );
  }
}
