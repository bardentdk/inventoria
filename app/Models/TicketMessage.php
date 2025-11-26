<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'user_id', 'message'];

    // Relation : Un message appartient à un Ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relation : Un message est écrit par un User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}