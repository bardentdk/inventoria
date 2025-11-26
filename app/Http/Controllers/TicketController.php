<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query()->with(['user']);

        // Recherche textuelle
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('id', 'like', '%'.$request->search.'%');
            });
        }

        // Filtre : Si ce n'est pas un admin, il ne voit que SES tickets
        // Si c'est un admin, il voit tout par défaut, sauf s'il filtre sur "me"
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // Filtre par statut (onglet)
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tickets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:it_issue,logistics,supply_request',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'asset_id' => 'nullable|exists:assets,id', // Le matériel est optionnel (ex: demande de chaise)
        ]);

        // Création du ticket lié à l'utilisateur connecté
        $ticket = Auth::user()->tickets()->create([
            ...$validated,
            'status' => 'open', // Statut par défaut
        ]);

        // TODO: Ici on ajoutera l'envoi de mail via Brevo plus tard

        return redirect()->route('dashboard')->with('success', 'Votre ticket a été créé avec succès.');
    }
    public function show(Ticket $ticket)
    {
        // On charge les relations nécessaires pour la vue
        $ticket->load(['user', 'asset.category', 'messages.user']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'auth_id' => auth()->id(), // Pour savoir qui est "moi" dans le chat
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->update(['status' => $request->status]);

        // Notification mail ici (TODO)

        return back()->with('success', 'Statut mis à jour.');
    }
}