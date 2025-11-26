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
        // On charge : la catégorie, l'utilisateur actuel, et les tickets (avec leur auteur)
        $asset->load(['category', 'user', 'tickets.user']);

        return Inertia::render('Assets/Show', [
            'asset' => $asset
        ]);
    }
}