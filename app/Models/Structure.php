<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // <--- IMPORT
use Spatie\Activitylog\LogOptions;

class Structure extends Model
{
    use LogsActivity;

    protected $fillable = ['name'];

    public function assets() {
        return $this->hasMany(Asset::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'structure_user');
    }
    // --- CONFIGURATION LOGS ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
