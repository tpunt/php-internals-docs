<?php

require_once './server/vendor/autoload.php';

use PHPInternalsDocs\Models\{Category, Article, Symbol};
use GraphAware\Neo4j\Client\ClientBuilder;

/*
Script to import the Graph DB into a flat-file representation.
*/

$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:neo@localhost:7474')
    ->build();

function addCategories($client)
{
    $categoriesQuery = $client->run(<<<END
MATCH (category:Category)

OPTIONAL MATCH (spc:Category)-[:SUBCATEGORY]->(category)

WITH COLLECT(spc.url) AS spcs, category

OPTIONAL MATCH (category)-[:SUBCATEGORY]->(sbc:Category)

WITH COLLECT(sbc.url) AS sbcs, spcs, category

OPTIONAL MATCH (category)<-[:CATEGORY]-(s:Symbol)

WITH COLLECT(s.id) AS symbols, sbcs, spcs, category

OPTIONAL MATCH (category)<-[:CATEGORY]-(a:Article)

WITH COLLECT(a.url) AS articles, symbols, sbcs, spcs, category

RETURN {
    name: category.name,
    url: category.url,
    body: category.introduction,
    subcategories: sbcs,
    supercategories: spcs,
    symbols: symbols,
    articles: articles
} AS category
END
);

    foreach ($categoriesQuery->getRecords() as $record) {
        $category = new Category();
        $catval = $record->value('category');

        $category->name = $catval['name'];
        $category->url = $catval['url'];
        $category->body = $catval['body'];
        $category->subcategories = $catval['subcategories'];
        $category->supercategories = $catval['supercategories'];
        $category->symbols = $catval['symbols'];
        $category->articles = $catval['articles'];

        file_put_contents("../docs/categories/{$category->url}.info", $category);
    }
}

function addArticles($client)
{
    $articlesQuery = $client->run(<<<END
MATCH (article:Article)
RETURN {
    title: article.title,
    url: article.url,
    date: article.date,
    series_name: article.series_name,
    series_url: article.series_url,
    excerpt: article.excerpt,
    body: article.body
} AS article
END
);

    foreach ($articlesQuery->getRecords() as $record) {
        $article = new Article();
        $artval = $record->value('article');

        $article->title = $artval['title'];
        $article->url = $artval['url'];
        $article->date = $artval['date'];
        $article->series_name = $artval['series_name'];
        $article->series_url = $artval['series_url'];
        $article->excerpt = $artval['excerpt'];
        $article->body = $artval['body'];

        file_put_contents("../docs/articles/{$article->url}.info", $article);
    }
}

function addSymbols($client)
{
    $symbolsQuery = $client->run(<<<END
MATCH (symbol:Symbol)
RETURN {
    id: symbol.id,
    name: symbol.name,
    url: symbol.url,
    type: symbol.type,
    declaration: symbol.declaration,
    parameters: symbol.parameters,
    definition: symbol.definition,
    source_location: symbol.source_location,
    description: symbol.description,
    additional_information: symbol.additional_information
} AS symbol
END
);

    foreach ($symbolsQuery->getRecords() as $record) {
        $symbol = new Symbol();
        $symval = $record->value('symbol');

        $symbol->id = $symval['id'];
        $symbol->name = $symval['name'];
        $symbol->url = $symval['url'];
        $symbol->type = $symval['type'];
        $symbol->declaration = $symval['declaration'];
        $symbol->parameters = $symval['parameters'];
        $symbol->definition = $symval['definition'];
        $symbol->source_location = $symval['source_location'];
        $symbol->description = $symval['description'];
        $symbol->additional_information = $symval['additional_information'];

        file_put_contents("../docs/symbols/{$symbol->id}.info", $symbol);
    }
}

// addCategories($client);
// addArticles($client);
// addSymbols($client);
