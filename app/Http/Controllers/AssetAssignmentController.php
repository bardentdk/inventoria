<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class AssetAssignmentController extends Controller
{
    // Attribuer un matériel
    public function store(Request $request, Asset $asset)
    {
        // Seul l'admin peut attribuer
        if ($request->user()->role !== 'admin') abort(403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assigned_at' => 'required|date',
            'comments' => 'nullable|string'
        ]);

        // 1. Créer l'historique
        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $validated['user_id'],
            'admin_id' => auth()->id(),
            'assigned_at' => $validated['assigned_at'],
            'comments' => $validated['comments'],
        ]);

        // 2. Mettre à jour le statut du matériel
        $asset->update([
            'status' => 'assigned',
            'user_id' => $validated['user_id'] // On garde la liaison directe pour l'affichage rapide
        ]);

        return back()->with('success', 'Matériel attribué avec succès.');
    }

    // Marquer comme rendu
    public function markAsReturned(Request $request, Asset $asset)
    {
        if ($request->user()->role !== 'admin') abort(403);

        // 1. Trouver l'attribution active
        $assignment = $asset->currentAssignment;

        if ($assignment) {
            $assignment->update([
                'returned_at' => now(),
            ]);
        }

        // 2. Libérer le matériel
        $asset->update([
            'status' => 'available',
            'user_id' => null
        ]);

        return back()->with('success', 'Matériel marqué comme rendu.');
    }
}