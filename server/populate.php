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
    'symbols_compact' => []
];

foreach ($articles_files as $file) {
    $article = Parser::parse($file, 'Article');
    $data['articles'][$article->url] = $article;
}

foreach ($symbols_files as $file) {
    $symbol = Parser::parse($file, 'Symbol');
    $data['symbols'][$symbol->id] = $symbol;
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

// delayed due to category adding
foreach ($data['articles'] as $article) {
    $data['articles_compact'][$article->url] = clone $article;
}

// delayed due to category adding
foreach ($data['symbols'] as $symbol) {
    $data['symbols_compact'][$symbol->id] = clone $symbol;
}

return $data;
