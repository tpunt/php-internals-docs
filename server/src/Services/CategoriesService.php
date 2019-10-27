<?php

namespace PHPInternalsDocs\Services;

use PHPInternalsDocs\Models\Error;

/*
Fetch all categories:
 - https://phpinternals.net/api/categories?limit=20&offset=0

Fetch one category:
 - https://phpinternals.net/api/categories/compiler
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

    public static function fetchCategory($data, $category_url)
    {
        if (isset($data['categories'][$category_url])) {
            return ['code' => 200, 'body' => $data['categories'][$category_url]];
        }

        return ['code' => 404, 'body' => new Error('Category not found')];
    }
}
