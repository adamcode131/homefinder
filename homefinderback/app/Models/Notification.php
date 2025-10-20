<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['owner_id' , 'lead_id' , 'deducted_points' , 'added_points'] ;
    public function Lead(){
        return $this->belongsTo(Lead::class);
    } 
}
