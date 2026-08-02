package com.shopio.app;

import android.app.DownloadManager;
import android.content.Context;
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

        // Le fond d'écran de démarrage (logo Shopio) reste défini comme fond de la
        // fenêtre après la fin du splash tant que la page n'a pas fini de se dessiner
        // par-dessus : il "transparaît" dans les zones encore vides pendant un
        // chargement lent. On force un fond blanc immédiat sur la WebView pour éviter ça.
        getBridge().getWebView().setBackgroundColor(Color.WHITE);

        // Filet de sécurité absolu : sur un réseau très lent, la page peut mettre
        // du temps à envoyer ne serait-ce que son tout début (qui masque normalement
        // l'écran de démarrage). On force donc sa disparition après 3s dans tous les
        // cas, pour qu'il ne reste jamais "coincé" indéfiniment à l'écran.
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            getBridge().getWebView().evaluateJavascript(
                "if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.SplashScreen) {" +
                "  window.Capacitor.Plugins.SplashScreen.hide();" +
                "}",
                null
            );
        }, 3000);

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
}
