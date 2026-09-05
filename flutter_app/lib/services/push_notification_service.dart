import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_client.dart';
import '../navigator_key.dart';
import '../screens/messages/conversations_screen.dart';
import '../screens/orders/orders_screen.dart';

/// Reçu par le système même app fermée/arrière-plan (obligatoire : doit être
/// une fonction top-level ou statique, exécutée dans un isolate séparé).
@pragma('vm:entry-point')
Future<void> _firebaseBackgroundHandler(RemoteMessage message) async {
  // Rien à faire ici : Android affiche déjà la notification système
  // automatiquement pour les messages avec un bloc "notification" (avec son,
  // voir PushService::sendFcm côté serveur). La navigation au clic est gérée
  // par onMessageOpenedApp/getInitialMessage (voir plus bas), pas ici.
}

/// Ouvre l'écran correspondant à l'URL envoyée par le serveur dans le payload
/// (voir PushService::sendToUser — 'data' => ['url' => ...]). Les seules URLs
/// pertinentes côté client sont /client/messages (réponse du vendeur) et
/// /client/orders (statut de commande mis à jour) — voir
/// Boutique\BoutiqueMessageController / Employe|Vendeur\OrderController.
void _routeFromUrl(String? url) {
  if (url == null) return;
  final nav = navigatorKey.currentState;
  if (nav == null) return;

  if (url.contains('/client/messages')) {
    nav.push(MaterialPageRoute(builder: (_) => const ConversationsScreen()));
  } else if (url.contains('/client/orders')) {
    nav.push(MaterialPageRoute(builder: (_) => const OrdersScreen()));
  }
}

/// Notifications push FCM avec son — même service que le site (PushService,
/// type=fcm réutilisé), mais côté client il faut explicitement afficher +
/// faire sonner la notification quand l'app est ouverte au premier plan
/// (Android ne le fait pas tout seul dans ce cas), et gérer nous-mêmes la
/// navigation quand l'utilisateur appuie sur la notification.
class PushNotificationService {
  final ApiClient _api;
  PushNotificationService(this._api);

  static final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  static const _channel = AndroidNotificationChannel(
    'shopio_default',
    'Notifications Shopio',
    description: 'Commandes, messages et offres Shopio',
    importance: Importance.max,
    playSound: true,
  );
  static bool _initialized = false;
  // App fermée puis ouverte via une notification : le NavigatorState n'existe
  // pas encore à cet instant (initOnce tourne avant runApp) → on garde l'URL
  // de côté et on navigue une fois l'app affichée (voir consumePendingTap()).
  static String? _pendingUrl;

  /// À appeler une seule fois au démarrage de l'app (avant tout login).
  static Future<void> initOnce() async {
    if (_initialized) return;
    _initialized = true;

    FirebaseMessaging.onBackgroundMessage(_firebaseBackgroundHandler);

    await _local
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    await _local.initialize(
      const InitializationSettings(android: AndroidInitializationSettings('@mipmap/ic_launcher')),
      onDidReceiveNotificationResponse: (details) => _routeFromUrl(details.payload),
    );

    // App au premier plan : FCM ne montre rien tout seul → on l'affiche nous-même.
    FirebaseMessaging.onMessage.listen((message) {
      final notif = message.notification;
      if (notif == null) return;
      _local.show(
        message.hashCode,
        notif.title,
        notif.body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            _channel.id,
            _channel.name,
            channelDescription: _channel.description,
            importance: Importance.max,
            priority: Priority.high,
            playSound: true,
            icon: '@mipmap/ic_launcher',
          ),
        ),
        payload: message.data['url'],
      );
    });

    // App en arrière-plan, l'utilisateur appuie sur la notification système.
    FirebaseMessaging.onMessageOpenedApp.listen((message) => _routeFromUrl(message.data['url']));

    // App fermée, ouverte directement via une notification (démarrage à froid).
    final initial = await FirebaseMessaging.instance.getInitialMessage();
    if (initial != null) _pendingUrl = initial.data['url'];
  }

  /// À appeler une fois l'app affichée (ex: dans AuthGate.initState) pour
  /// terminer la navigation d'un démarrage à froid depuis une notification.
  static void consumePendingTap() {
    if (_pendingUrl == null) return;
    final url = _pendingUrl;
    _pendingUrl = null;
    _routeFromUrl(url);
  }

  /// Demande la permission (Android 13+) puis enregistre le jeton FCM auprès
  /// du serveur — à appeler après connexion (login/OTP) et au démarrage si déjà connecté.
  Future<void> subscribe() async {
    try {
      final settings = await FirebaseMessaging.instance.requestPermission(alert: true, badge: true, sound: true);
      if (settings.authorizationStatus == AuthorizationStatus.denied) return;

      final token = await FirebaseMessaging.instance.getToken();
      if (token == null) return;

      await _api.safeCall(() => _api.dio.post('/push/subscribe', data: {'endpoint': token, 'type': 'fcm'}));

      // Le jeton peut changer (réinstallation, restauration...) : on le réenregistre.
      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
        _api.safeCall(() => _api.dio.post('/push/subscribe', data: {'endpoint': newToken, 'type': 'fcm'}));
      });
    } catch (_) {
      // Permission refusée ou Firebase indisponible : l'app continue sans push.
    }
  }

  /// À appeler à la déconnexion — retire ce téléphone des envois futurs.
  Future<void> unsubscribe() async {
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token == null) return;
      await _api.safeCall(() => _api.dio.post('/push/unsubscribe', data: {'endpoint': token}));
    } catch (_) {}
  }
}
