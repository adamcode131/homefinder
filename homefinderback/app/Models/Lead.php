<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'property_id',
        'owner_id',
        'name',
        'email',
        'phone',
        'status',
        'date_reservation'
    ];
    public function property(){
        return $this->belongsTo(Property::class);
    } 

    public function Refunds(){
        return $this->hasMany(Refund::class);
    } 

    public function owner(){
        return $this->belongsTo(User::class , 'owner_id')->where('role', 'owner');
    }
}
