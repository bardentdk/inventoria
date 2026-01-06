<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // On met à jour sans déclencher l'update_at pour ne pas spammer
            auth()->user()->updateQuietly([
                'last_activity_at' => now(),
                'last_login_ip' => $request->ip()
            ]);
        }

        return $next($request);
    }
}