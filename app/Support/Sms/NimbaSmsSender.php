<?php

namespace App\Support\Sms;

use App\Support\PhoneKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Envoi de SMS par Nimba SMS (https://api.nimbasms.com/v1/messages), avec le Service ID et le Secret Token
 * du compte (authentification de base) et un nom d'expéditeur approuvé.
 * Format repris de la documentation et de l'outil officiel de Nimba : sender_name, to (liste de numéros
 * +224…), message. Identifiants dans .env : NIMBA_SERVICE_ID, NIMBA_SECRET_TOKEN, NIMBA_SENDER_NAME.
 */
class NimbaSmsSender implements SmsSender
{
    public const URL = 'https://api.nimbasms.com/v1/messages';

    public function __construct(private string $serviceId, private string $secret, private string $senderName) {}

    public static function configured(): bool
    {
        return filled(config('services.nimba.service_id')) && filled(config('services.nimba.secret')) && filled(config('services.nimba.sender'));
    }

    public function send(string $phone, string $message): bool
    {
        $key = PhoneKey::of($phone);
        if (!$key) {
            return false;
        }
        try {
            $response = Http::withBasicAuth($this->serviceId, $this->secret)->acceptJson()->timeout(10)
                ->post(self::URL, ['sender_name' => $this->senderName, 'to' => ['+224' . $key], 'message' => $message]);
            if ($response->successful()) {
                return true;
            }
            Log::warning('[SMS Nimba] envoi refusé (' . $response->status() . ') : ' . Str::limit($response->body(), 300));
        } catch (\Throwable $e) {
            Log::warning('[SMS Nimba] envoi impossible : ' . $e->getMessage());
        }
        return false;
    }
}
