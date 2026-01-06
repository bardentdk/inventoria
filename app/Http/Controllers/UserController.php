<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Notifications\AccountCreated;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // AJOUT : on charge 'structures' pour pouvoir afficher les badges dans le tableau
        $query = User::query()->with('structures');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        return Inertia::render('Users/Index', [
            'users' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'structures' => Structure::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,user',
            'password' => 'required|min:8',
            // AJOUT VALIDATION STRUCTURES
            'structures' => 'nullable|array', 
            'structures.*' => 'exists:structures,id', // Vérifie que chaque ID existe
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // CORRECTION : On utilise input(..., []) pour gérer le cas vide proprement
        $user->structures()->sync($request->input('structures', []));

        try {
            $user->notify(new AccountCreated($validated['password']));
        } catch (\Exception $e) {
            // Log silent
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur créé et notifié par email.');
    }

    public function edit(User $user)
    {
        $user->load('structures');
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'structures' => Structure::all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,user',
            'password' => 'nullable|min:8',
            // AJOUT VALIDATION STRUCTURES
            'structures' => 'nullable|array',
            'structures.*' => 'exists:structures,id',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // IMPORTANT : Utiliser sync avec input() par défaut tableau vide
        // Cela permet de tout détacher si l'admin décoche tout.
        $user->structures()->sync($request->input('structures', []));

        return redirect()->route('users.index')->with('success', 'Profil mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}