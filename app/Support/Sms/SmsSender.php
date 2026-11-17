<?php

namespace App\Support\Sms;

/**
 * Envoi de SMS : NimbaSmsSender si NIMBA_SERVICE_ID, NIMBA_SECRET_TOKEN et NIMBA_SENDER_NAME sont dans .env,
 * sinon LogSmsSender écrit les messages dans le journal (voir AppServiceProvider).
 */
interface SmsSender
{
    /** Envoie $message au numéro $phone. Renvoie false si l'envoi a échoué. */
    public function send(string $phone, string $message): bool;
}
