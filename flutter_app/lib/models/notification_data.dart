class MessageNotification {
  final int id;
  final String senderName;
  final String? shopName;
  final int? productId;
  final String? productName;
  final String body;
  final DateTime? createdAt;

  MessageNotification({
    required this.id,
    required this.senderName,
    this.shopName,
    this.productId,
    this.productName,
    required this.body,
    this.createdAt,
  });

  factory MessageNotification.fromJson(Map<String, dynamic> json) {
    return MessageNotification(
      id: json['id'],
      senderName: json['sender_name'] ?? 'Vendeur',
      shopName: json['shop_name'],
      productId: json['product_id'],
      productName: json['product_name'],
      body: json['body'] ?? '',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }
}

class OrderUpdateNotification {
  final int id;
  final String status;
  final String shopName;
  final double total;
  final DateTime? updatedAt;

  OrderUpdateNotification({required this.id, required this.status, required this.shopName, required this.total, this.updatedAt});

  factory OrderUpdateNotification.fromJson(Map<String, dynamic> json) {
    return OrderUpdateNotification(
      id: json['id'],
      status: json['status'] ?? '',
      shopName: json['shop_name'] ?? '',
      total: (json['total'] as num?)?.toDouble() ?? 0,
      updatedAt: json['updated_at'] != null ? DateTime.tryParse(json['updated_at']) : null,
    );
  }
}

class NotificationFeed {
  final int messagesUnread;
  final List<MessageNotification> latestMessages;
  final List<OrderUpdateNotification> orderUpdates;
  final int orderUpdatesUnseen;

  NotificationFeed({required this.messagesUnread, required this.latestMessages, required this.orderUpdates, required this.orderUpdatesUnseen});

  int get totalBadge => messagesUnread + orderUpdatesUnseen;

  factory NotificationFeed.fromJson(Map<String, dynamic> json) {
    return NotificationFeed(
      messagesUnread: json['messages_unread'] ?? 0,
      latestMessages: (json['latest_messages'] as List).map((e) => MessageNotification.fromJson(e)).toList(),
      orderUpdates: (json['order_updates'] as List).map((e) => OrderUpdateNotification.fromJson(e)).toList(),
      orderUpdatesUnseen: json['order_updates_unseen'] ?? 0,
    );
  }

  static NotificationFeed empty() => NotificationFeed(messagesUnread: 0, latestMessages: [], orderUpdates: [], orderUpdatesUnseen: 0);
}
