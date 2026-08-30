package com.shopio.app;

import android.app.DownloadManager;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.webkit.CookieManager;
import android.webkit.URLUtil;
import android.widget.Toast;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // App Links (voir AndroidManifest.xml) : si l'app est ouverte à froid via un
        // lien https://shopio-app.com/... (ex: retour de connexion Google depuis le
        // navigateur), on navigue directement vers CETTE page-là au lieu de toujours
        // recharger la page d'accueil configurée par défaut.
        handleIncomingUrl(getIntent());

        // Le fond d'écran de démarrage (logo Shopio) reste défini comme fond de la
        // fenêtre après la fin du splash tant que la page n'a pas fini de se dessiner
        // par-dessus : il "transparaît" dans les zones encore vides pendant un
        // chargement lent. On force un fond blanc immédiat sur la WebView pour éviter ça.
        getBridge().getWebView().setBackgroundColor(Color.WHITE);

        // Filet de sécurité en tout dernier recours seulement : la page elle-même
        // (layouts/app.blade.php) ferme normalement cet écran au bon moment, dès
        // qu'elle est réellement prête à s'afficher (voir sa propre sécurité à 35s
        // sur réseau très lent). Ce délai-ci est volontairement plus long (40s) pour
        // ne JAMAIS se déclencher avant elle en temps normal — il ne sert qu'à éviter
        // un écran bloqué indéfiniment si la page n'a même pas pu charger son JS du tout
        // (échec réseau total). Un délai plus court referait apparaître le même flash
        // de texte brut qu'on cherche justement à éliminer.
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            getBridge().getWebView().evaluateJavascript(
                "if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.SplashScreen) {" +
                "  window.Capacitor.Plugins.SplashScreen.hide();" +
                "}",
                null
            );
        }, 40000);

        // Empêche le réglage "taille de police / zoom d'écran" du téléphone de fausser
        // le calcul de largeur d'écran utilisé par nos mises en page responsives (CSS),
        // ce qui provoquait un affichage en 1 colonne au lieu de la grille attendue.
        getBridge().getWebView().getSettings().setTextZoom(100);

        // La WebView ne gère pas nativement les téléchargements (PDF, Excel...).
        // On délègue au gestionnaire de téléchargements natif d'Android, en
        // transmettant les cookies de session pour que la requête reste authentifiée.
        getBridge().getWebView().setDownloadListener((url, userAgent, contentDisposition, mimeType, contentLength) -> {
            try {
                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
                String cookies = CookieManager.getInstance().getCookie(url);
                request.addRequestHeader("cookie", cookies);
                request.addRequestHeader("User-Agent", userAgent);
                request.setMimeType(mimeType);
                String fileName = URLUtil.guessFileName(url, contentDisposition, mimeType);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName);
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                DownloadManager dm = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
                dm.enqueue(request);
                Toast.makeText(getApplicationContext(), "Téléchargement en cours...", Toast.LENGTH_LONG).show();
            } catch (Exception e) {
                Toast.makeText(getApplicationContext(), "Échec du téléchargement", Toast.LENGTH_LONG).show();
            }
        });
    }

    // L'app est en "singleTask" (voir AndroidManifest.xml) : si elle tourne déjà
    // en arrière-plan et qu'un lien https://shopio-app.com/... arrive (retour de
    // connexion Google depuis le navigateur), Android réutilise cette même
    // instance et appelle onNewIntent() au lieu de recréer l'activité.
    @Override
    public void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleIncomingUrl(intent);
    }

    // Si le lien contient une adresse shopio-app.com précise (ex: après connexion
    // Google), on y navigue directement au lieu de rester sur la page déjà ouverte
    // ou de recharger la page d'accueil par défaut.
    private void handleIncomingUrl(Intent intent) {
        if (intent == null || !Intent.ACTION_VIEW.equals(intent.getAction())) return;
        Uri data = intent.getData();
        if (data != null) {
            getBridge().getWebView().loadUrl(data.toString());
        }
    }
}
