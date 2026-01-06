<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // Important pour ton admin
        'department', // Optionnel
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELATIONS AJOUTÉES POUR NEXA ---

    /**
     * Un utilisateur a créé plusieurs tickets.
     * C'est cette méthode qui manquait et causait ton erreur.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    public function structures() {
        return $this->belongsToMany(Structure::class);
    }
    /**
     * Un utilisateur peut avoir plusieurs matériels assignés.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Un utilisateur a écrit plusieurs messages dans les chats.
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}