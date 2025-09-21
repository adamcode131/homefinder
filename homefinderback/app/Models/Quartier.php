<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    use HasFactory;

        public function ville(){
        return $this->belongsTo(Ville::class);
        }

        public function properties(){
            return $this->hasMany(Property::class);
        }
}
