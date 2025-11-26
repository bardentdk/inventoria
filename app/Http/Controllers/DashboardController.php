<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Asset;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        // On récupère quelques stats basiques pour les cartes Bento
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_assets' => Asset::count(),
                'available_assets' => Asset::where('status', 'available')->count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'my_tickets' => Ticket::where('user_id', auth()->id())->count(),
            ],
            // On récupère les 5 derniers tickets récents
            'recent_tickets' => Ticket::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($ticket) => [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'user' => $ticket->user->name,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'date' => $ticket->created_at->diffForHumans(),
                ]),
        ]);
    }
}