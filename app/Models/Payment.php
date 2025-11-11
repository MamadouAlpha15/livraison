<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'method', 'amount', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class); // Un paiement appartient à une commande
    }

    //Statuts (constantes)
    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_PAYE       = 'payé'; // ou 'paye' si tu préfères sans accent

   
}
