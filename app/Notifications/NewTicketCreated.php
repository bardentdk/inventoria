<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\BrevoChannel; // <--- Import Important

class NewTicketCreated extends Notification
{
    use Queueable;

    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    // On spécifie qu'on utilise notre canal HTTP personnalisé
    public function via(object $notifiable): array
    {
        return [BrevoChannel::class];
    }

    // On prépare les données pour Brevo
    public function toBrevo(object $notifiable): array
    {
        $url = route('tickets.show', $this->ticket->id);

        // HTML simple et propre pour le mail
        $html = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #2563EB;'>Nouveau Ticket Support (#{$this->ticket->id})</h2>
                <p>Bonjour <strong>{$notifiable->name}</strong>,</p>
                <p>Un nouveau ticket a été créé sur la plateforme NEXA.</p>

                <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>Titre :</strong> {$this->ticket->title}</p>
                    <p><strong>Auteur :</strong> {$this->ticket->user->name}</p>
                    <p><strong>Priorité :</strong> <span style='color:red'>{$this->ticket->priority}</span></p>
                </div>

                <p style='text-align: center;'>
                    <a href='{$url}' style='background-color: #2563EB; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Voir le ticket
                    </a>
                </p>

                <hr style='margin-top: 30px; border: none; border-top: 1px solid #eee;' />
                <p style='font-size: 12px; color: #888; text-align: center;'>NEXA - Gestion de Parc Informatique</p>
            </div>
        ";

        return [
            'subject' => "Nouveau Ticket : " . $this->ticket->title,
            'content' => $html
        ];
    }
}