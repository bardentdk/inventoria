<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- Ligne Importante 1
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory; // <--- Ligne Importante 2

    protected $fillable = ['name'];

    // Relation inverse (optionnelle mais utile)
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}