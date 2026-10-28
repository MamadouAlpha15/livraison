<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Rappel envoyé à un client ou une entreprise de livraison inactif (App\Services\InactivityReminderService). */
class InactivityReminder extends Model
{
    use HasFactory;

    public const AUDIENCE_CLIENT  = 'client';
    public const AUDIENCE_COMPANY = 'company';

    public const AUDIENCES = [
        self::AUDIENCE_CLIENT  => 'Clients',
        self::AUDIENCE_COMPANY => 'Entreprises de livraison',
    ];

    public const CHANNELS = ['push' => 'Notification', 'mail' => 'E-mail', 'sms' => 'SMS'];

    protected $fillable = ['user_id', 'audience', 'step', 'channels', 'sent_at', 'returned_at'];

    protected $casts = [
        'step'        => 'integer',
        'channels'    => 'array',
        'sent_at'     => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
