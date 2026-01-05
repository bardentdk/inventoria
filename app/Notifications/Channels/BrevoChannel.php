<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toBrevo')) {
            return;
        }

        $data = $notification->toBrevo($notifiable);
        $email = $notifiable->email;
        $name = $notifiable->name;

        // --- CORRECTION ICI : On utilise config() ---
        $apiKey = config('services.brevo.key');
        $senderEmail = config('services.brevo.sender_email');
        $senderName = config('services.brevo.sender_name');

        // Petit check de sécurité pour le debug
        if (empty($apiKey)) {
            Log::error('BREVO: Clé API manquante dans la configuration services.php');
            return;
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey, // Utilisation de la variable chargée via config
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
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

        if ($response->failed()) {
            Log::error('Erreur Brevo API:', $response->json());
        }
    }
}