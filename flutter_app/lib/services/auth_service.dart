import '../models/user.dart';
import 'api_client.dart';

class AuthService {
  final ApiClient _api;
  AuthService(this._api);

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
  }) async {
    final res = await _api.safeCall(() => _api.dio.post('/auth/register', data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'country': country,
          'terms': terms,
          if (phone != null) 'phone': phone,
          if (address != null) 'address': address,
          if (ref != null && ref.isNotEmpty) 'ref': ref,
        }));
    return res.data['user_id'];
  }

  /// POST /auth/google/complete — finalise un nouveau compte après connexion Google.
  Future<AppUser> completeGoogleSignup({
    required String googleId,
    required String googleName,
    required String googleEmail,
    required String country,
    required bool terms,
  }) async {
    final res = await _api.safeCall(() => _api.dio.post('/auth/google/complete', data: {
          'google_id': googleId,
          'google_name': googleName,
          'google_email': googleEmail,
          'country': country,
          'terms': terms,
        }));
    await ApiClient.saveToken(res.data['token']);
    return AppUser.fromJson(res.data['user']);
  }

  /// Retour de connexion Google réussie (jeton déjà émis par le serveur) : on
  /// le sauvegarde puis on récupère le profil, comme après un login classique.
  Future<AppUser?> loginWithGoogleToken(String token) async {
    await ApiClient.saveToken(token);
    return me();
  }

  Future<AppUser> verifyOtp({required int userId, required String code}) async {
    final res = await _api.safeCall(() => _api.dio.post('/auth/verify-otp', data: {
          'user_id': userId,
          'code': code,
        }));
    await ApiClient.saveToken(res.data['token']);
    return AppUser.fromJson(res.data['user']);
  }

  Future<void> resendOtp(int userId) async {
    await _api.safeCall(() => _api.dio.post('/auth/resend-otp', data: {'user_id': userId}));
  }

  /// Retourne l'utilisateur connecté, ou lève ApiException avec
  /// data['needs_verification'] == true si le compte n'est pas encore vérifié
  /// (data['user_id'] permet alors d'enchaîner directement sur verifyOtp).
  Future<AppUser> login({required String email, required String password}) async {
    final res = await _api.safeCall(() => _api.dio.post('/auth/login', data: {
          'email': email,
          'password': password,
        }));
    await ApiClient.saveToken(res.data['token']);
    return AppUser.fromJson(res.data['user']);
  }

  Future<AppUser?> me() async {
    try {
      final res = await _api.dio.get('/auth/me');
      return AppUser.fromJson(res.data['user']);
    } catch (_) {
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _api.dio.post('/auth/logout');
    } catch (_) {}
    await ApiClient.clearToken();
  }
}
