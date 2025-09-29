<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EntityFilterValue extends Model
{
    use HasFactory; 
        protected $fillable = [
        'entity_id',
        'entity_type',
        'filter_option_id',
    ];

    /**
     * Relation avec l'option de filtre
     */
    public function filterOption(): BelongsTo
    {
        return $this->belongsTo(FilterOption::class);
    }   

    /**
     * Relation polymorphe avec l'entité (Product ou System)
     */
    public function entity()
    {
        return $this->morphTo(null, 'entity_type', 'entity_id');
    }

    /**
     * Scope pour filtrer par type d'entité
     */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope pour filtrer par entité spécifique
     */
    public function scopeForEntity($query, int $entityId, string $entityType)
    {
        return $query->where('entity_id', $entityId)
            ->where('entity_type', $entityType);
    }   
}
