<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\BrevoChannel;

class AccountCreated extends Notification
{
    use Queueable;

    public $rawPassword; // On stocke le mot de passe en clair juste pour l'envoi

    public function __construct($rawPassword)
    {
        $this->rawPassword = $rawPassword;
    }

    public function via(object $notifiable): array
    {
        return [BrevoChannel::class];
    }

    public function toBrevo(object $notifiable): array
    {
        $url = route('login');

        $html = "
            <div style='font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #2563EB;'>Bienvenue chez NEXA !</h2>
                <p>Bonjour <strong>{$notifiable->name}</strong>,</p>
                <p>Votre compte administrateur/utilisateur vient d'être créé.</p>
                
                <div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin:0 0 10px 0;'>Voici vos identifiants de connexion :</p>
                    <p><strong>Email :</strong> {$notifiable->email}</p>
                    <p><strong>Mot de passe provisoire :</strong> <span style='background: white; padding: 2px 5px; border-radius: 4px; font-family: monospace;'>{$this->rawPassword}</span></p>
                </div>

                <p style='color: #ef4444; font-size: 13px;'>
                    ⚠️ Par sécurité, nous vous invitons à changer ce mot de passe dès votre première connexion via la page 'Mon Profil'.
                </p>

                <p style='text-align: center; margin-top: 30px;'>
                    <a href='{$url}' style='background-color: #2563EB; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                        Se connecter à NEXA
                    </a>
                </p>
            </div>
        ";

        return [
            'subject' => "Vos identifiants d'accès NEXA",
            'content' => $html
        ];
    }
}