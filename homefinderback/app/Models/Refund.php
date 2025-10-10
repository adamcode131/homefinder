<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory; 
    protected $fillable = ['lead_id','reason','status'] ;  

    public function lead(){
        return $this->belongsTo(Lead::class);
    }
}
