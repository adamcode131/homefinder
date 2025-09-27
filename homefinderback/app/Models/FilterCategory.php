<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterCategory extends Model
{
    use HasFactory; 
        protected $fillable = [
        'name',
        'slug', 
        'type',
        'entity_types',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'entity_types' => 'array'
    ];

    public function options()
    {
        return $this->hasMany(FilterOption::class)->orderBy('sort_order');
    }

    public function activeOptions()
    {
        return $this->hasMany(FilterOption::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope pour filtrer les catégories qui s'appliquent à un type d'entité donné
     */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->whereJsonContains('entity_types', $entityType);
    }

    /**
     * Vérifier si cette catégorie s'applique à un type d'entité
     */
    public function appliesToEntity(string $entityType): bool
    {
        return in_array($entityType, $this->entity_types ?? ['property']);
    }

    /**
     * Vérifier si cette catégorie s'applique aux produits
     */
    public function appliesToProducts(): bool
    {
        return $this->appliesToEntity('property');
    }

    /**
     * Vérifier si cette catégorie s'applique aux systèmes
     */
    public function appliesToSystems(): bool
    {
        return $this->appliesToEntity('system');
    }
}
