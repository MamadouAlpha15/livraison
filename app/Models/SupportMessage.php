<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = ['ticket_id','user_id','body'];

    // Chaque message appartient à un ticket
    public function ticket()
    {
        // ⚠️ idem : on indique la FK réelle
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    // Auteur du message (pour ->load('messages.author'))
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
