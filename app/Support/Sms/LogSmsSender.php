<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Log;

/** SMS écrits dans le journal (storage/logs) au lieu d'être envoyés : en attendant un fournisseur. */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): bool
    {
        Log::info('[SMS] ' . $phone . ' : ' . $message);
        return true;
    }
}
