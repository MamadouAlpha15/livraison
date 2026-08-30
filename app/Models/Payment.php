<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'amount', 'status',
        'gateway_operation_id', 'paid_at',
        'payout_status', 'platform_fee_amount', 'payout_amount', 'payout_reference', 'payout_sent_at',
    ];

    protected $casts = [
        'paid_at'        => 'datetime',
        'payout_sent_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class); // Un paiement appartient à une commande
    }

    //Statuts (constantes)
    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_PAYE       = 'payé'; // ou 'paye' si tu préfères sans accent

    // Méthodes de paiement
    public const METHOD_CASH       = 'cash';
    public const METHOD_CHAPCHAPPAY = 'chapchappay';

    // Statuts de reversement à la boutique (colonne payout_status)
    public const PAYOUT_DUE        = 'due';        // payé en ligne, reversement pas encore fait
    public const PAYOUT_PROCESSING = 'processing';  // demande de règlement envoyée à ChapChap Pay
    public const PAYOUT_SENT       = 'sent';        // règlement exécuté
    public const PAYOUT_FAILED     = 'failed';      // règlement échoué (à relancer)
}
