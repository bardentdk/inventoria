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
        // Dans la méthode boot()
        Event::listen(Login::class, function ($event) {
            $event->user->update(['last_login_at' => now(), 'last_login_ip' => request()->ip()]);
            
            activity()
                ->causer($event->user)
                ->withProperties([
                    'ip' => request()->ip(), 
                    'agent' => request()->header('User-Agent')
                ])
                ->log('Connexion à la plateforme');
        });
    }
}
