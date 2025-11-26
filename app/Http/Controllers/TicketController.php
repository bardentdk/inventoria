<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewTicketCreated;
use Illuminate\Support\Facades\Notification;

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
        // --- ENVOI DE LA NOTIFICATION ---

        // 1. On trouve les administrateurs (ceux qui doivent recevoir l'alerte)
        $admins = User::where('role', 'admin')->get();

        // 2. On envoie la notif (Laravel gère la boucle automatiquement)
        // On utilise Notification::send pour envoyer à une collection d'users
        if ($admins->count() > 0) {
            Notification::send($admins, new NewTicketCreated($ticket));
        }

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
    public function edit(Ticket $ticket)
    {
        // Seul l'auteur ou l'admin peut éditer
        if (auth()->user()->role !== 'admin' && auth()->id() !== $ticket->user_id) {
            abort(403);
        }

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $ticket->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:it_issue,logistics,supply_request',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Ticket mis à jour.');
    }

    public function destroy(Ticket $ticket)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent supprimer des tickets.');
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket supprimé définitivement.');
    }
}