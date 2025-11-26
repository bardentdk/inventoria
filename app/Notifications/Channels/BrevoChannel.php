<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoChannel
{
    /**
     * Envoie la notification via l'API Brevo.
     */
    public function send($notifiable, Notification $notification)
    {
        // 1. On vérifie si la méthode toBrevo existe dans la notification
        if (!method_exists($notification, 'toBrevo')) {
            return;
        }

        // 2. On récupère les données formatées par la notification
        $data = $notification->toBrevo($notifiable);

        // 3. On récupère l'email du destinataire (User)
        $email = $notifiable->email;
        $name = $notifiable->name;

        // 4. Appel HTTP vers Brevo
        $response = Http::withHeaders([
            'api-key' => env('BREVO_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => env('BREVO_SENDER_NAME', 'NEXA'),
                'email' => env('BREVO_SENDER_EMAIL'),
            ],
            'to' => [
                [
                    'email' => $email,
                    'name' => $name
                ]
            ],
            'subject' => $data['subject'],
            'htmlContent' => $data['content'],
        ]);

        // 5. Petit log en cas d'erreur pour le debug
        if ($response->failed()) {
            Log::error('Erreur Brevo API:', $response->json());
        }
    }
}