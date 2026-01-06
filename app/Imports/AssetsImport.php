<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class AssetsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row);
        // 1. Gestion Intelligente de la Catégorie
        // L'utilisateur met "PC Portable" dans le fichier, on cherche l'ID ou on le crée.
        $category = Category::firstOrCreate(
            ['name' => $row['categorie']], // On cherche par le nom
            ['name' => $row['categorie']]  // Si pas trouvé, on crée avec ce nom
        );

        // 2. Mapping des Status (Français -> Code BDD)
        $statusMap = [
            'disponible' => 'available',
            'assigné' => 'assigned',
            'réparation' => 'repair',
            'hs' => 'broken',
        ];

        // Par défaut 'available' si le statut n'est pas reconnu
        $status = $statusMap[strtolower($row['statut'] ?? '')] ?? 'available';

        return new Asset([
            'name'           => $row['nom_du_materiel'],
            'serial_number'  => $row['numero_de_serie'],
            'inventory_code' => $row['code_inventaire'],
            'specs'          => $row['specifications'] ?? null,
            'status'         => $status,
            'category_id'    => $category->id,
            'user_id'        => null, // On n'assigne pas d'user à l'import de masse pour éviter les erreurs
        ]);
    }

    // Validation des données du fichier
    public function rules(): array
    {
        return [
            'nom_du_materiel' => 'required',
            'numero_de_serie' => 'required|unique:assets,serial_number',
            'code_inventaire' => 'required|unique:assets,inventory_code',
            'categorie'       => 'required',
        ];
    }

    // Configuration CSV
    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
            'delimiter' => ',', // <--- ON PASSE À LA VIRGULE (Standard)
        ];
    }
}