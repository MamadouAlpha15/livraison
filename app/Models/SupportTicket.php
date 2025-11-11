<?php

// app/Models/SupportTicket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['shop_id','user_id','subject','status'];

   

    // Un ticket a plusieurs messages
    public function messages()
    {
        // ⚠️ on précise la FK réelle 'ticket_id' pour éviter 'support_ticket_id'
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    // Créateur du ticket (si tu l’utilises dans ->load(['creator', ...]))
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Boutique liée (si utilisée)
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}

