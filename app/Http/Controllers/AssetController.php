<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        // Récupération avec Recherche et Pagination
        $query = Asset::query()->with('category');

        if ($request->input('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                  ->orWhere('inventory_code', 'like', '%'.$request->input('search').'%')
                  ->orWhere('serial_number', 'like', '%'.$request->input('search').'%');
            });
        }

        // Filtre par statut (optionnel, pour plus tard)
        if ($request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        return Inertia::render('Assets/Index', [
            'assets' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Assets/Create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|unique:assets',
            'inventory_code' => 'required|unique:assets',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:available,assigned,broken,repair',
            'specs' => 'nullable|string'
        ]);

        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Matériel ajouté avec succès.');
    }
    // Dans App\Http\Controllers\AssetController

    public function search(Request $request)
    {
        // Retourne du JSON pour les composants Vue (Combobox)
        return Asset::query()
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('inventory_code', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'serial_number', 'inventory_code']);
    }
    public function show(Asset $asset)
    {
        $asset->load(['category', 'user', 'tickets.user', 'assignments.user', 'assignments.admin']);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
            // On envoie la liste des users pour le selecteur d'attribution
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name'])
        ]);
    }
    public function edit(Asset $asset)
    {
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'categories' => Category::all() // On a besoin de la liste pour le menu déroulant
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // On ignore l'ID actuel pour la validation unique
            'serial_number' => 'required|unique:assets,serial_number,' . $asset->id,
            'inventory_code' => 'required|unique:assets,inventory_code,' . $asset->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:available,assigned,broken,repair',
            'specs' => 'nullable|string'
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Matériel mis à jour.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Matériel supprimé de l\'inventaire.');
    }
}