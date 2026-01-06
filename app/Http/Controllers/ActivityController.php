<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        // Seul l'admin voit ça
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $query = Activity::query()->with('causer')->latest();

        // --- FILTRE PAR PERSONNE ---
        if ($request->user_id) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\Models\User');
        }

        // --- RECHERCHE GLOBALE ---
        if ($request->search) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        return Inertia::render('Activity/Index', [
            'activities' => $query->paginate(20)->withQueryString(),
            'users' => User::orderBy('name')->get(['id', 'name']), // Pour le select
            'filters' => $request->only(['user_id', 'search']),
        ]);
    }

    public function export(Request $request)
    {
        $filename = 'logs-activite-' . date('Y-m-d-H-i') . '.csv';

        $query = Activity::query()->with('causer')->latest();

        if ($request->user_id) {
            $query->where('causer_id', $request->user_id);
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
            fputs($file, "\xEF\xBB\xBF");

            // ENTÊTES HYPER COMPLÈTES
            fputcsv($file, [
                'Date Heure', 
                'Utilisateur', 
                'Action', 
                'Sujet concerné (ID)', 
                'Détails Modifications (Ancien -> Nouveau)',
                'IP',
                'Navigateur'
            ], ';');

            $query->chunk(500, function($activities) use ($file) {
                foreach ($activities as $log) {
                    
                    // Formatage des changements (Ancienne valeur -> Nouvelle valeur)
                    $changes = '';
                    if ($log->properties && isset($log->properties['attributes'])) {
                        foreach ($log->properties['attributes'] as $key => $newVal) {
                            $oldVal = $log->properties['old'][$key] ?? 'N/A';
                            // On évite d'afficher les dates systèmes qui polluent
                            if($key !== 'updated_at') {
                                $changes .= "[$key : $oldVal -> $newVal] ";
                            }
                        }
                    }

                    // Récupération IP/Agent si stocké dans properties (pour le Login)
                    $ip = $log->properties['ip'] ?? '';
                    $agent = $log->properties['agent'] ?? '';

                    fputcsv($file, [
                        $log->created_at->format('d/m/Y H:i:s'),
                        $log->causer ? $log->causer->name : 'Système',
                        $log->description, // Ex: "updated" ou "created"
                        class_basename($log->subject_type) . ' #' . $log->subject_id,
                        $changes ?: 'Aucun détail de modification',
                        $ip,
                        $agent
                    ], ';');
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}