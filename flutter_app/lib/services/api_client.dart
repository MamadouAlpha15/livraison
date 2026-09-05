import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/api_config.dart';

/// Erreur API avec le message renvoyé par le serveur (déjà en français, prêt
/// à afficher directement à l'utilisateur — Laravel renvoie des messages
/// clairs comme "Identifiants incorrects.").
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? data;
  ApiException(this.message, {this.statusCode, this.data});
  @override
  String toString() => message;
}

/// Client HTTP central : ajoute automatiquement le jeton de connexion sur
/// chaque requête, et transforme les erreurs Laravel en ApiException lisible.
class ApiClient {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'shopio_token';

  late final Dio dio;

  ApiClient() {
    dio = Dio(BaseOptions(
      baseUrl: ApiConfig.baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ));

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
    ));
  }

  static Future<String?> getToken() => _storage.read(key: _tokenKey);
  static Future<void> saveToken(String token) => _storage.write(key: _tokenKey, value: token);
  static Future<void> clearToken() => _storage.delete(key: _tokenKey);

  /// Enveloppe un appel Dio et transforme toute erreur en ApiException avec
  /// le message Laravel (ex: "Cette adresse email est déjà utilisée.").
  Future<Response> safeCall(Future<Response> Function() call) async {
    try {
      return await call();
    } on DioException catch (e) {
      final data = e.response?.data;
      String message = 'Une erreur est survenue. Vérifiez votre connexion.';
      if (data is Map && data['message'] != null) {
        message = data['message'].toString();
      } else if (data is Map && data['errors'] != null) {
        final errors = data['errors'] as Map;
        message = errors.values.first is List ? errors.values.first.first.toString() : errors.values.first.toString();
      }
      throw ApiException(message, statusCode: e.response?.statusCode, data: data is Map<String, dynamic> ? data : null);
    }
  }
}
