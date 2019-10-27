<?php

namespace PHPInternalsDocs\Services;

/*
Fetch all categories:
 - https://phpinternals.net/api/categories?limit=20&offset=0
*/

class CategoriesService
{
    public static function fetchCategories($data, $query)
    {
        $categories = array_values($data['categories_compact']);

        $ordering = $query['ordering'] ?? 'asc';

        switch ($ordering) {
            case 'asc':
            case 'desc':
                usort($categories, function ($a, $b) use ($ordering) {
                    if ($ordering === 'desc') {
                        return $a->name < $b->name;
                    }

                    return $a->name > $b->name;
                });
        }

        return ['code' => 200, 'body' => $categories];
    }
}
