<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Méthode simple pour l'ajout rapide (AJAX)
    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name'
        ]);

        $category = Category::create($validated);

        // On retourne l'objet créé en JSON
        return response()->json($category);
    }
}