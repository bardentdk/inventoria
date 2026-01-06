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
        // --- FILTRE DONATION ---
        // Si on clique sur "Voir les donations", on affiche que ça.
        // Sinon par défaut, on peut décider d'afficher tout ou juste le stock classique.
        if ($request->has('show_donations') && $request->show_donations == 'true') {
            $query->where('is_donation', true);
        } 
        // Optionnel : Si tu veux cacher les donations de la liste principale, décommente ça :
        else {
           $query->where('is_donation', false);
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
            'structure_id' => 'nullable|exists:structures,id',
            'is_donation' => 'boolean',
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
            'is_donation' => $request->has('is_donation') ? $request->is_donation : false,
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
        // AJOUT DE 'structure' dans le load pour l'affichage fiche détail
        $asset->load(['category', 'user', 'structure', 'tickets.user', 'assignments.user', 'assignments.admin']);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
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
            'structure_id' => 'nullable|exists:structures,id',
            'is_donation' => 'boolean',
            // Validation conditionnelle : Requis seulement si donation cochée et qu'on veut valider le don
            'donation_recipient' => 'nullable|string|max:255',
            'donation_date' => 'nullable|date',
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
            // --- LIGNE AJOUTÉE ICI ---
            'is_donation' => $validated['is_donation'] ?? false, 
            // Si c'est une donation, on enregistre les infos, sinon on remet à null pour nettoyer
            'donation_recipient' => ($validated['is_donation'] ?? false) ? ($validated['donation_recipient'] ?? null) : null,
            'donation_date' => ($validated['is_donation'] ?? false) ? ($validated['donation_date'] ?? null) : null,
        ]);

        return redirect()->route('assets.index')->with('success', 'Matériel mis à jour.');
    }
    public function export(Request $request)
    {
        $filename = 'inventaire-nexa-' . date('Y-m-d-H-i') . '.csv';

        // 1. On charge toutes les relations
        $query = Asset::query()->with(['category', 'user', 'structure']);

        // 2. Filtre Recherche
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('inventory_code', 'like', '%'.$request->search.'%')
                  ->orWhere('serial_number', 'like', '%'.$request->search.'%');
            });
        }

        // 3. Filtre Structure
        if ($request->structure) {
            $query->where('structure_id', $request->structure);
        }

        // 4. Filtre Donation (Même logique que l'écran)
        if ($request->has('show_donations') && $request->show_donations == 'true') {
            $query->where('is_donation', true);
        } else {
            $query->where('is_donation', false);
        }

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel

            // --- ENTÊTES COMPLÈTES ---
            fputcsv($file, [
                'Nom', 
                'S/N', 
                'Code Inventaire', 
                'Catégorie', 
                'Structure', 
                'Type',                    // Inventaire ou Donation
                'Statut', 
                'Utilisateur (Interne)',   // Si assigné à un employé
                'Bénéficiaire (Donation)', // Si donné à l'extérieur
                'Date du Don', 
                'Spécifications / Notes',  // Toutes les infos techniques
                'Date Ajout'
            ], ';');

            $query->chunk(100, function($assets) use ($file) {
                foreach ($assets as $asset) {
                    fputcsv($file, [
                        $asset->name,
                        $asset->serial_number,
                        $asset->inventory_code,
                        $asset->category ? $asset->category->name : 'N/A',
                        $asset->structure ? $asset->structure->name : '',
                        $asset->is_donation ? 'DONATION' : 'INVENTAIRE',
                        $asset->status,
                        // Utilisateur interne (si lié à un compte User)
                        $asset->user ? $asset->user->name : '',
                        // Infos Donation (si c'est un don externe)
                        $asset->donation_recipient ?? '',
                        $asset->donation_date ? $asset->donation_date->format('d/m/Y') : '',
                        // Notes techniques
                        $asset->specs ?? '',
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