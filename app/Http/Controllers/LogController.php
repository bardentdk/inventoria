<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Sécurité Admin
        if ($request->user()->role !== 'admin') abort(403);

        $logs = Activity::with('causer') // "Causer" = Celui qui a fait l'action
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Logs', [
            'logs' => $logs
        ]);
    }
}