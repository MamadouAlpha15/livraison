<?php

namespace App\Support\Sms;

/**
 * Envoi de SMS. Aucun fournisseur n'est branché pour l'instant : LogSmsSender écrit les messages
 * dans le journal. Pour un vrai fournisseur, créer une classe qui implémente cette interface
 * (identifiants dans .env) et la déclarer dans AppServiceProvider.
 */
interface SmsSender
{
    /** Envoie $message au numéro $phone. Renvoie false si l'envoi a échoué. */
    public function send(string $phone, string $message): bool;
}
