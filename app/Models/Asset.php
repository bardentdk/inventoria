<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- Ligne Importante 1
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory; // <--- Ligne Importante 2

    protected $fillable = [
        'name',
        'serial_number',
        'inventory_code',
        'category_id',
        'user_id',
        'status',
        'specs'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}