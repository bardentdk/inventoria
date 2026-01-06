<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TicketMessageController;

/*
|--------------------------------------------------------------------------
| Web Routes - NEXA v1.0
|--------------------------------------------------------------------------
|
| Architecture complète des routes pour l'application d'inventaire et de
| ticketing. Tout est sécurisé par le middleware 'auth' sauf le login.
|
*/

// --- RACINE ---
// Redirection automatique vers le login (pas de landing page publique)
Route::get('/', function () {
    return redirect()->route('login');
});

// --- ZONE INVITÉS (GUEST) ---
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

// --- ZONE PROTÉGÉE (AUTH) ---
Route::middleware('auth')->group(function () {

    // 1. Authentification & Système
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestion de l'Inventaire (Assets)
    // Route spéciale pour l'autocomplete (Recherche JSON pour les selecteurs)
    Route::get('assets/search', [AssetController::class, 'search'])->name('assets.search');
    // Export Excel/CSV
    Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::resource('assets', AssetController::class);

    // 3. Gestion des Tickets (Support)
    // Routes spécifiques pour les actions métier (hors CRUD standard)
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assignTechnician'])->name('tickets.assign');

    // Route pour poster un message (chat) dans un ticket
    Route::post('tickets/{ticket}/messages', [TicketMessageController::class, 'store'])->name('tickets.messages.store');

    // CRUD standard des tickets
    Route::resource('tickets', TicketController::class);

    // 4. Gestion des Utilisateurs (Administration)
    // On pourrait ajouter un middleware 'can:admin' ici plus tard pour blinder
    Route::resource('users', UserController::class);

    // Gestion des Attributions
    Route::post('assets/{asset}/assign', [App\Http\Controllers\AssetAssignmentController::class, 'store'])->name('assets.assign');
    Route::post('assets/{asset}/return', [App\Http\Controllers\AssetAssignmentController::class, 'markAsReturned'])->name('assets.return');
    Route::post('/assets/destroy-all', [App\Http\Controllers\AssetController::class, 'destroyAll'])->name('assets.destroy-all');
    Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
    #Création des catégories
    Route::post('api/categories', [CategoryController::class, 'storeApi'])->name('categories.store.api');

    Route::get('/admin/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');

    // Gestion du Profil
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/activity', [App\Http\Controllers\ActivityController::class, 'index'])->name('activity.index');
    Route::get('/activity/export', [App\Http\Controllers\ActivityController::class, 'export'])->name('activity.export');
}); 