<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketMessageController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        // Petit bonus UX : Si l'admin répond, on passe le ticket "En cours" s'il était ouvert
        if (Auth::user()->role === 'admin' && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back(); // Inertia rechargera la liste des messages automatiquement
    }
}