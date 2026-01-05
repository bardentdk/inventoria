<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model
{
    use HasFactory, LogsActivity;

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

    // Configuration des logs
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // On liste précisément ce qu'on veut garder en mémoire
            ->logOnly(['title', 'description', 'status'])
            // On force l'enregistrement même si rien n'a "changé" (cas de la suppression)
            ->dontSubmitEmptyLogs();
    }
}