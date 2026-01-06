<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Imports\AssetsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Category;
use App\Models\Structure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        // Récupération avec Recherche et Pagination
        $query = Asset::query()->with('category', 'user', 'structure');

        if ($request->input('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                  ->orWhere('inventory_code', 'like', '%'.$request->input('search').'%')
                  ->orWhere('serial_number', 'like', '%'.$request->input('search').'%');
            });
        }
        // --- NOUVEAU FILTRE STRUCTURE ---
        if ($request->structure) {
            $query->where('structure_id', $request->structure);
        }
        // Filtre par statut (optionnel, pour plus tard)
        if ($request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        return Inertia::render('Assets/Index', [
            'assets' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'structure']),
            'structures' => \App\Models\Structure::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Assets/Create', [
            'categories' => Category::all(),
            'structures' => Structure::all(),
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
            'structure_id' => 'nullable|exists:structures,id'
        ]);

        // 1. Création du matériel
        $asset = Asset::create([
            'name' => $validated['name'],
            'serial_number' => $validated['serial_number'],
            'inventory_code' => $validated['inventory_code'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'specs' => $validated['specs'] ?? null, // Utilisation de ?? null par sécurité
            // Si assigné, on lie directement, sinon null
            'user_id' => $validated['status'] === 'assigned' ? ($validated['user_id'] ?? null) : null,
            // On récupère la structure ou null si non définie
            'structure_id' => $validated['structure_id'] ?? null, 
        ]);

        // 2. Si on l'a assigné immédiatement, on crée l'historique (AssetAssignment)
        if ($validated['status'] === 'assigned' && !empty($validated['user_id'])) {
            \App\Models\AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $validated['user_id'],
                'admin_id' => auth()->id(),
                'assigned_at' => now(),
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
            'structures' => Structure::all(),
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
            'specs' => 'nullable|string',
            'structure_id' => 'nullable|exists:structures,id'
        ]);

        // Mise à jour propre
        $asset->update([
            'name' => $validated['name'],
            'serial_number' => $validated['serial_number'],
            'inventory_code' => $validated['inventory_code'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'specs' => $validated['specs'] ?? null,
            'structure_id' => $validated['structure_id'] ?? null,
        ]);

        return redirect()->route('assets.index')->with('success', 'Matériel mis à jour.');
    }
    public function export(Request $request)
    {
        $filename = 'inventaire-nexa-' . date('Y-m-d-H-i') . '.csv';

        // 1. On charge la relation 'structure' pour l'afficher dans le CSV
        $query = Asset::query()->with(['category', 'user', 'structure']);

        // 2. On applique le filtre de Recherche
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('inventory_code', 'like', '%'.$request->search.'%')
                  ->orWhere('serial_number', 'like', '%'.$request->search.'%');
            });
        }

        // 3. IMPORTANT : On applique le filtre Structure (comme dans l'index)
        // Sinon l'utilisateur exporte tout même s'il a filtré une structure précise
        if ($request->structure) {
            $query->where('structure_id', $request->structure);
        }

        // Headers HTTP
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8

            // 4. On ajoute la colonne 'Structure' dans l'entête
            fputcsv($file, ['Nom', 'S/N', 'Code Inventaire', 'Catégorie', 'Structure', 'Statut', 'Utilisateur Actuel', 'Date Ajout'], ';');

            $query->chunk(100, function($assets) use ($file) {
                foreach ($assets as $asset) {
                    fputcsv($file, [
                        $asset->name,
                        $asset->serial_number,
                        $asset->inventory_code,
                        $asset->category ? $asset->category->name : 'N/A',
                        // 5. On ajoute la donnée Structure
                        $asset->structure ? $asset->structure->name : '', 
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
    public function import(Request $request)
    {
        // 1. Validation
        // On garde 'txt' pour la tolérance, au cas où
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            // 2. On lance l'import RÉEL (plus de dd)
            Excel::import(new AssetsImport, $request->file('file'));

            // 3. Message de succès
            return back()->with('success', 'Importation réussie ! Votre inventaire est à jour.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Erreur précise (ex: Numéro de série déjà pris)
            $failures = $e->failures();
            $errorMsg = 'Erreur ligne ' . $failures[0]->row() . ' : ' . $failures[0]->errors()[0];
            
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            // Erreur technique générale
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
    public function destroyAll(Request $request)
    {
        // 1. Sécurité : Seul un admin peut faire ça
        if ($request->user()->role !== 'admin') {
            abort(403, 'Action non autorisée.');
        }

        // 2. Validation du mot de passe pour confirmer
        $request->validate([
            'password' => 'required|current_password', // Vérifie que le mot de passe est celui de l'admin connecté
        ]);

        try {
            // 3. Suppression de masse
            // On utilise delete() au lieu de truncate() pour que les événements (logs) soient déclenchés si besoin
            // et pour respecter les contraintes de clés étrangères (si un ticket est lié).
            Asset::query()->delete();

            return back()->with('success', 'Inventaire entièrement vidé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de vider l\'inventaire : ' . $e->getMessage());
        }
    }
}