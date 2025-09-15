<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory; 

    protected  $fillable = [
        'title',
        'description',
        'purpose',
        'type',
        'ville',
        'quartier',
        'sale_price',
        'rent_price',
        'owner_id',
    ];


    public function getFormattedPriceAttribute(){
            return $this->purpose === 'rent'
            ? $this->rent_price . ' dh / month'
            : $this->sale_price . ' dh';
    } 

    
    public function owner(){
        return $this->belongsTo(User::class, 'owner_id')->where('role', 'owner');
    }

    public function lead(){
        return $this->hasMany(Lead::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

}
