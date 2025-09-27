<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterOption extends Model
{
    use HasFactory;
        protected $fillable = [
        'filter_category_id',
        'name',
        'slug',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function filterCategory()
    {
        return $this->belongsTo(FilterCategory::class, 'filter_category_id');
    }

    public function Property()
    {
        return $this->belongsToMany(Property::class, 'entity_filter_values', 'filter_option_id', 'entity_id')
            ->where('entity_filter_values.entity_type', 'product');
    }

    /**
     * Relation with systems through entity filter values
     */
    public function systems()
    {
        return $this->belongsToMany(System::class, 'entity_filter_values', 'filter_option_id', 'entity_id')
            ->where('entity_filter_values.entity_type', 'system');
    }
}
