<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Quartier;

class Ville extends Model
{
    use HasFactory; 

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    } 

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
