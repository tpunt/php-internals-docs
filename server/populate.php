<?php

use PHPInternalsDocs\Services\Parser;

$articles_files = glob('../docs/articles/*.info');
$categories_files = glob('../docs/categories/*.info');
$symbols_files = glob('../docs/symbols/*.info');

$data = [
    'articles' => [],
    'articles_compact' => [],
    'categories' => [],
    'categories_compact' => [],
    'symbols' => [],
    'symbols_compact' => [],
    'symbols_ids_by_url' => [],
];

foreach ($articles_files as $file) {
    $article = Parser::parse($file, 'Article');
    $data['articles'][$article->url] = $article;
}

foreach ($symbols_files as $file) {
    $symbol = Parser::parse($file, 'Symbol');
    $data['symbols'][$symbol->id] = $symbol;
    $data['symbols_ids_by_url'][$symbol->url][] = $symbol->id;
}

foreach ($categories_files as $file) {
    $category = Parser::parse($file, 'Category');
    $data['categories'][$category->url] = $category;

    foreach ($category->articles as $article_url) {
        $data['articles'][$article_url]->categories[$category->url] = $category->name;
    }

    foreach ($category->symbols as $symbol_id) {
        $data['symbols'][$symbol_id]->categories[$category->url] = $category->name;
    }

    $data['categories_compact'][$category->url] = clone $category;
}

foreach ($data['categories'] as $category_url => $category) {
    $subcategories = [];
    $supercategories = [];

    foreach ($data['categories'][$category_url]->subcategories as $subcategory) {
        $subcategories[$subcategory] = $data['categories'][$subcategory]->name;
    }

    foreach ($data['categories'][$category_url]->supercategories as $supercategory) {
        $supercategories[$supercategory] = $data['categories'][$supercategory]->name;
    }

    $data['categories'][$category_url]->subcategories = $subcategories;
    $data['categories'][$category_url]->supercategories = $supercategories;
    $data['categories_compact'][$category_url]->subcategories = $subcategories;
    $data['categories_compact'][$category_url]->supercategories = $supercategories;
}

// delayed due to category adding
foreach ($data['articles'] as $article) {
    $data['articles_compact'][$article->url] = clone $article;
}

// delayed due to category adding
foreach ($data['symbols'] as $symbol) {
    $data['symbols_compact'][$symbol->id] = clone $symbol;
}

return $data;
