<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory; 

    protected $fillable = [
        'title',
        'description',
        'type',
        'intention',
        'ville_id',
        'quartier_id',
        'rent_price',
        'sale_price',
        'owner_id',
    ];


    public function getFormattedPriceAttribute(){
            return $this->intention === 'loyer'
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


    public function quartier(){
        return $this->belongsTo(Quartier::class);
    }

    
    // filter config 

    
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getIsNewAttribute()
    {
        return $this->created_at->diffInDays(now()) <= 30; // Product is new if created within the last 30 days
    }

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }


    public function filterValues(): HasMany
    {
        return $this->hasMany(EntityFilterValue::class, 'entity_id')
            ->where('entity_type', self::class);
    }

    
    public function filterOptions(): BelongsToMany
    {
        return $this->belongsToMany(FilterOption::class, 'entity_filter_values', 'entity_id', 'filter_option_id')
            ->where('entity_filter_values.entity_type', self::class)
            ->withTimestamps();
    }
}
