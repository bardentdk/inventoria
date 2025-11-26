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
            'categories' => Category::all(),
            // On ajoute la liste des utilisateurs pour le select
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name'])
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
            'specs' => 'nullable|string',
            // Validation conditionnelle : user_id est requis SI le statut est 'assigned'
            'user_id' => 'required_if:status,assigned|nullable|exists:users,id',
        ]);

        // 1. Création du matériel
        $asset = Asset::create([
            'name' => $validated['name'],
            'serial_number' => $validated['serial_number'],
            'inventory_code' => $validated['inventory_code'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'specs' => $validated['specs'],
            // Si assigné, on lie directement, sinon null
            'user_id' => $validated['status'] === 'assigned' ? $validated['user_id'] : null,
        ]);

        // 2. Si on l'a assigné immédiatement, on crée l'historique (AssetAssignment)
        // pour garder une trace propre dans la Timeline
        if ($validated['status'] === 'assigned' && $request->user_id) {
            \App\Models\AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $validated['user_id'],
                'admin_id' => auth()->id(),
                'assigned_at' => now(), // Date du jour
                'comments' => 'Assignation initiale à la création du matériel.',
            ]);
        }

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
    public function export(Request $request)
    {
        $filename = 'inventaire-nexa-' . date('Y-m-d-H-i') . '.csv';

        // On reprend les mêmes filtres que l'index pour exporter ce qu'on voit (ou tout si pas de filtre)
        $query = Asset::query()->with(['category', 'user']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('inventory_code', 'like', '%'.$request->search.'%')
                  ->orWhere('serial_number', 'like', '%'.$request->search.'%');
            });
        }

        // Headers HTTP pour forcer le téléchargement
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Fonction de callback qui écrit le fichier en flux (stream)
        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');

            // Astuce pour Excel : BOM UTF-8 pour les accents
            fputs($file, "\xEF\xBB\xBF");

            // Ligne d'entête
            fputcsv($file, ['Nom', 'S/N', 'Code Inventaire', 'Catégorie', 'Statut', 'Utilisateur Actuel', 'Date Ajout'], ';');

            // On traite par paquets de 100 pour ne pas surcharger la RAM
            $query->chunk(100, function($assets) use ($file) {
                foreach ($assets as $asset) {
                    fputcsv($file, [
                        $asset->name,
                        $asset->serial_number,
                        $asset->inventory_code,
                        $asset->category ? $asset->category->name : 'N/A',
                        $asset->status,
                        $asset->user ? $asset->user->name : 'Non assigné',
                        $asset->created_at->format('d/m/Y'),
                    ], ';');
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Matériel supprimé de l\'inventaire.');
    }
}