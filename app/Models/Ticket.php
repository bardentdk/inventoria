<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // C'est ici qu'on autorise l'écriture dans la DB
    protected $fillable = [
        'user_id',
        'assigned_to',
        'asset_id',
        'title',
        'description',
        'type',
        'priority',
        'status'
    ];

    // --- RELATIONS ---

    // Le créateur du ticket
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le technicien assigné (optionnel)
    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Le matériel lié (optionnel)
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Les messages du chat
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}