class ChatMessage {
  final int id;
  final String? body;
  final String? note;
  final bool mine;
  final String? sender;
  final String type; // text | price_proposal | price_offer | price_counter | order_created | images
  final double? proposedPrice;
  final String? proposalStatus; // pending | accepted | refused
  final List<String> images;
  final bool read;
  final DateTime? createdAt;

  ChatMessage({
    required this.id,
    this.body,
    this.note,
    required this.mine,
    this.sender,
    this.type = 'text',
    this.proposedPrice,
    this.proposalStatus,
    this.images = const [],
    this.read = false,
    this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'],
      body: json['body'],
      note: json['note'],
      mine: json['mine'] ?? false,
      sender: json['sender'],
      type: json['type'] ?? 'text',
      proposedPrice: json['proposed_price'] != null ? double.tryParse(json['proposed_price'].toString()) : null,
      proposalStatus: json['proposal_status'],
      images: (json['images'] as List?)?.map((e) => e.toString()).toList() ?? [],
      read: json['read'] ?? false,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }
}

class Conversation {
  final int shopId;
  final String? shopName;
  final int productId;
  final String? productName;
  final String? lastMessage;
  final DateTime? lastAt;
  final int unread;

  Conversation({
    required this.shopId,
    this.shopName,
    required this.productId,
    this.productName,
    this.lastMessage,
    this.lastAt,
    this.unread = 0,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      shopId: json['shop_id'] ?? 0,
      shopName: json['shop_name'],
      productId: json['product_id'] ?? 0,
      productName: json['product_name'],
      lastMessage: json['last_message'],
      lastAt: json['last_at'] != null ? DateTime.tryParse(json['last_at']) : null,
      unread: json['unread'] ?? 0,
    );
  }
}
