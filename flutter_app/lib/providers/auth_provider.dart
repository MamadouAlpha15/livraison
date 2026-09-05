import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../services/push_notification_service.dart';

enum AuthStatus { checking, loggedOut, loggedIn }

class AuthProvider extends ChangeNotifier {
  final AuthService _authService;
  final PushNotificationService _push;
  AuthProvider(ApiClient api) : _authService = AuthService(api), _push = PushNotificationService(api);

  AuthStatus status = AuthStatus.checking;
  AppUser? user;

  /// Vrai quand l'utilisateur parcourt l'app sans compte (voir continueAsGuest).
  /// Comme sur le site : catalogue, fiche produit et commande directe restent
  /// accessibles ; panier, favoris, commandes, messages et profil demandent
  /// de se connecter.
  bool guestMode = false;

  void continueAsGuest() {
    guestMode = true;
    notifyListeners();
  }

  /// Appelé au démarrage de l'app : vérifie si un jeton valide est déjà enregistré.
  Future<void> checkAuth() async {
    final token = await ApiClient.getToken();
    if (token == null) {
      status = AuthStatus.loggedOut;
      notifyListeners();
      return;
    }
    final u = await _authService.me();
    if (u != null) {
      user = u;
      status = AuthStatus.loggedIn;
      _push.subscribe(); // réenregistre le jeton FCM à chaque démarrage (peut avoir changé)
    } else {
      await ApiClient.clearToken();
      status = AuthStatus.loggedOut;
    }
    notifyListeners();
  }

  Future<int> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String country,
    required bool terms,
    String? phone,
    String? address,
    String? ref,
  }) {
    return _authService.register(
      name: name,
      email: email,
      password: password,
      passwordConfirmation: passwordConfirmation,
      country: country,
      terms: terms,
      phone: phone,
      address: address,
      ref: ref,
    );
  }

  /// Connexion Google réussie (jeton déjà émis) — voir DeepLinkService.
  Future<void> loginWithGoogleToken(String token) async {
    final u = await _authService.loginWithGoogleToken(token);
    if (u == null) return;
    user = u;
    status = AuthStatus.loggedIn;
    guestMode = false;
    notifyListeners();
    _push.subscribe();
  }

  /// Finalise un nouveau compte Google (pays + CGU) — voir GoogleSetupScreen.
  Future<void> completeGoogleSignup({
    required String googleId,
    required String googleName,
    required String googleEmail,
    required String country,
    required bool terms,
  }) async {
    user = await _authService.completeGoogleSignup(
      googleId: googleId, googleName: googleName, googleEmail: googleEmail, country: country, terms: terms,
    );
    status = AuthStatus.loggedIn;
    guestMode = false;
    notifyListeners();
    _push.subscribe();
  }

  Future<void> verifyOtp({required int userId, required String code}) async {
    user = await _authService.verifyOtp(userId: userId, code: code);
    status = AuthStatus.loggedIn;
    guestMode = false;
    notifyListeners();
    _push.subscribe();
  }

  Future<void> resendOtp(int userId) => _authService.resendOtp(userId);

  Future<void> login({required String email, required String password}) async {
    user = await _authService.login(email: email, password: password);
    status = AuthStatus.loggedIn;
    guestMode = false;
    notifyListeners();
    _push.subscribe();
  }

  Future<void> logout() async {
    await _push.unsubscribe(); // avant de perdre le jeton d'auth, sinon la requête serait rejetée
    await _authService.logout();
    user = null;
    status = AuthStatus.loggedOut;
    guestMode = false;
    notifyListeners();
  }
}
