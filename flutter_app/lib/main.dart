import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'services/api_client.dart';
import 'services/push_notification_service.dart';
import 'services/deep_link_service.dart';
import 'navigator_key.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'screens/main_shell.dart';
import 'screens/auth/login_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    // Réutilise le projet Firebase existant (google-services.json partagé
    // avec l'app Capacitor, même package com.shopio.app) — pas d'options
    // explicites nécessaires sur Android.
    await Firebase.initializeApp();
    await PushNotificationService.initOnce();
  } catch (_) {
    // Firebase indisponible (ex: build sans google-services.json) : l'app
    // continue de fonctionner normalement, simplement sans notifications push.
  }
  runApp(const ShopioApp());
}

class ShopioApp extends StatelessWidget {
  const ShopioApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<ApiClient>(create: (_) => ApiClient()),
        ChangeNotifierProvider<AuthProvider>(create: (ctx) => AuthProvider(ctx.read<ApiClient>())..checkAuth()),
        ChangeNotifierProvider<CartProvider>(create: (ctx) => CartProvider(ctx.read<ApiClient>())),
      ],
      child: MaterialApp(
        navigatorKey: navigatorKey,
        title: 'Shopio',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorSchemeSeed: const Color(0xFF6366F1),
          navigationBarTheme: const NavigationBarThemeData(
            indicatorColor: Color(0xFFEEF2FF),
          ),
        ),
        home: const AuthGate(),
        // Android transmet aussi les liens entrants (App Links) au système de
        // routes nommées de Flutter en plus du flux DeepLinkService — sans ce
        // filet, une URL sans route nommée correspondante provoquait une
        // exception interne ("onUnknownRoute"). On l'ignore simplement : la
        // vraie navigation est gérée par DeepLinkService/PushNotificationService.
        onUnknownRoute: (settings) => null,
      ),
    );
  }
}

/// Décide quel écran afficher selon l'état de connexion : écran de démarrage
/// pendant la vérification du jeton, puis connexion ou app principale.
class AuthGate extends StatefulWidget {
  const AuthGate({super.key});
  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  @override
  void initState() {
    super.initState();
    // Écoute le retour de "Continuer avec Google" (Android App Links) pour
    // toute la durée de vie de l'app.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      DeepLinkService.init(context);
      // Termine la navigation si l'app vient d'être ouverte depuis une
      // notification (démarrage à froid) — voir PushNotificationService.
      PushNotificationService.consumePendingTap();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, _) {
        switch (auth.status) {
          case AuthStatus.checking:
            return const Scaffold(
              backgroundColor: Color(0xFF6366F1),
              body: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.storefront, color: Colors.white, size: 64),
                    SizedBox(height: 16),
                    Text('Shopio', style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w900)),
                    SizedBox(height: 24),
                    CircularProgressIndicator(color: Colors.white),
                  ],
                ),
              ),
            );
          case AuthStatus.loggedOut:
            return auth.guestMode ? const MainShell() : const LoginScreen();
          case AuthStatus.loggedIn:
            return const MainShell();
        }
      },
    );
  }
}
