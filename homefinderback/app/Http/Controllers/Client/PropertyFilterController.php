<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use App\Models\FilterOption;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyFilterController extends Controller
{
        public function getFilters()
    {
        $filters = FilterCategory::forEntityType('Property')
            ->with('activeOptions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

            Log::info($filters);
        return response()->json([
            'filters' => $filters
        ]);
    }

    public function filterProperties(Request $request)
    {
       
        $query = Property::with(['sector', 'filterValues.filterOption.filterCategory']);

        // Appliquer les filtres
        if ($request->has('filters') && !empty($request->filters)) {
            $filterIds = $request->filters;
            
            // Ensure filters is an array
            if (is_string($filterIds)) {
                $filterIds = explode(',', $filterIds);
            }
            
            if (!is_array($filterIds)) {
                return response()->json(['error' => 'Invalid filters format'], 400);
            }
            
            // Convert string IDs to integers and filter out invalid ones
            $filterIds = array_filter(array_map('intval', $filterIds), function($id) {
                return $id > 0;
            });
            
            if (!empty($filterIds)) {
                // Récupérer toutes les options de filtre en une seule requête
                $filterOptions = FilterOption::with('filterCategory')->whereIn('id', $filterIds)->get();
                
                // Grouper les filtres par catégorie pour un filtrage AND entre catégories
                $filtersByCategory = [];
                foreach ($filterOptions as $filterOption) {
                    $filtersByCategory[$filterOption->filterCategory->id][] = $filterOption->id;
                }

                // Appliquer les filtres (AND entre catégories, OR dans la même catégorie)
                foreach ($filtersByCategory as $categoryId => $categoryFilters) {
                    $query->whereHas('filterValues', function ($q) use ($categoryFilters) {
                        $q->whereIn('filter_option_id', $categoryFilters);
                    });
                }
            }
        }

        // Recherche par nom (case-insensitive, driver aware)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $driver = DB::getDriverName();
            $query->where(function ($q) use ($search, $driver) {
                if ($driver === 'pgsql') {
                    $q->where('name', 'ILIKE', "%{$search}%");
                } else {
                    $lower = mb_strtolower($search);
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$lower}%"]);
                }
            });
        }

        // Filtrage par secteur
        if ($request->has('sector_id') && !empty($request->sector_id)) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filtrage par statut online
        if ($request->has('online')) {
            $query->where('online', $request->boolean('online'));
        }

        // Pagination
        $Properties = $query->paginate(20);

        // Ajouter les filtres appliqués à chaque produit
        $Properties->getCollection()->transform(function ($Property) {
            $Property->applied_filters = $Property->filterValues()->with('filterOption.filterCategory')->get()->groupBy('filterOption.filterCategory.name');
            return $Property;
        });

        return response()->json($Properties);
    }

    public function getFilterStats(Request $request)
    {
        // Récupérer les filtres actuellement sélectionnés
        $selectedFilters = $request->get('filters', []);
        if (is_string($selectedFilters)) {
            $selectedFilters = explode(',', $selectedFilters);
        }
        $selectedFilters = array_filter(array_map('intval', $selectedFilters));

        // Grouper les filtres sélectionnés par catégorie
        $selectedFiltersByCategory = [];
        if (!empty($selectedFilters)) {
            $filterOptions = FilterOption::with('filterCategory')->whereIn('id', $selectedFilters)->get();
            foreach ($filterOptions as $filterOption) {
                $selectedFiltersByCategory[$filterOption->filterCategory->id][] = $filterOption->id;
            }
        }

        // Récupérer toutes les catégories avec leurs options pour les produits uniquement
        $categories = FilterCategory::forEntityType('Property')
            ->with('activeOptions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Pour chaque catégorie et option, calculer le nombre de produits
        foreach ($categories as $category) {
            foreach ($category->activeOptions as $option) {
                // Construire une requête de base
                $query = Property::query();

                // Appliquer les filtres des autres catégories (pas la catégorie actuelle)
                foreach ($selectedFiltersByCategory as $catId => $filterIds) {
                    if ($catId != $category->id) {
                        $query->whereHas('filterValues', function ($q) use ($filterIds) {
                            $q->whereIn('filter_option_id', $filterIds);
                        });
                    }
                }

                // Ajouter ce filtre spécifique pour compter
                $query->whereHas('filterValues', function ($q) use ($option) {
                    $q->where('filter_option_id', $option->id);
                });

                // Appliquer la recherche si fournie (case-insensitive, driver aware)
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $driver = DB::getDriverName();
                    $query->where(function ($q) use ($search, $driver) {
                        if ($driver === 'pgsql') {
                            $q->where('name', 'ILIKE', "%{$search}%");
                        } else {
                            $lower = mb_strtolower($search);
                            $q->whereRaw('LOWER(name) LIKE ?', ["%{$lower}%"]);
                        }
                    });
                }

                $option->Property_count = $query->count();
            }
        }

        return response()->json([
            'filter_stats' => $categories
        ]);
    }
}
