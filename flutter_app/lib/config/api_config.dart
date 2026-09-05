/// Adresse de l'API Shopio. En debug sur émulateur Android, 10.0.2.2 pointe
/// vers le PC hôte ; sur un vrai téléphone (USB/wifi), on utilise directement
/// l'adresse du site en production, qui est toujours disponible.
class ApiConfig {
  static const String baseUrl = 'https://shopio-app.com/api/v1';
}
