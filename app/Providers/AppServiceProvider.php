<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- ECOUTEUR DE CONNEXION ---
        Event::listen(Login::class, function ($event) {
            
            // 1. On crée une entrée dans le journal d'activité
            activity()
                ->causedBy($event->user) // L'utilisateur qui se connecte
                ->withProperties([
                    'ip' => request()->ip(),
                    'agent' => request()->header('User-Agent'), // Le navigateur (Chrome, Firefox...)
                ])
                ->event('login') // Type d'événement
                ->log('Connexion à la plateforme'); // Le message visible

            // 2. On met aussi à jour la table users (pour avoir l'info rapidement sans fouiller les logs)
            $event->user->updateQuietly([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
        });
    }
}
