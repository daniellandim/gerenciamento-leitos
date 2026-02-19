<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'cpf'];

    public function occupancies(): HasMany
    {
        return $this->hasMany(BedOccupancy::class);
    }

    public function currentOccupancy(): HasOne
    {
        return $this->hasOne(BedOccupancy::class)->whereNull('discharged_at');
    }

    public function isAdmitted(): bool
    {
        return $this->currentOccupancy()->exists();
    }
}
