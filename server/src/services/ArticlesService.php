<?php

namespace PHPInternalsDocs\Services;

/*
View all articles:
 - https://phpinternals.net/api/articles?limit=10&ordering=desc&offset=0&cb=42651414140

View one article:
 - https://phpinternals.net/api/articles/optimising_internal_functions_via_new_opcode_instructions?cb=51550225580
 Then loop through article categories to fetch related articles:
  - https://phpinternals.net/api/articles?category=opcodes&ordering=desc&view=full&cb=51550225580
  - https://phpinternals.net/api/articles?category=optimisations&ordering=desc&view=full&cb=51550225580
 Also fetch articles for that article's series (same link as an article link):
  - https://phpinternals.net/api/articles/php_range_operator?cb=79327469630

Params to support:
 - category
 - limit
 - offset
 - ordering
*/

class ArticlesService
{
    public static function fetchArticles($data, $query)
    {
        if (isset($query['category'])) {
            return self::fetchArticlesByCategory($data, $query['category'], $query);
        }

        $articles = array_values($data['articles_compact']);

        if (isset($query['ordering'])) {
            // TODO order here
        }

        return [
            'code' => 200,
            'body' => $articles
        ];
    }

    public static function fetchArticle($article_url, $data, $query)
    {
        // May return multiple articles if the article specified is a series

        $series = self::filterArticlesBySeries($article_url, $data, $query);

        if ($series) {
            // return the series
        }

        // return the article
    }

    public static function filterArticlesByCategory($data, $category, $query)
    {
        $articles = [];

        if (!isset($data['categories'][$category])) {
            return ['code' => 404, 'response' => 'Category not found'];
        }

        foreach ($data['categories'][$category]->article_urls as $article_url) {
            $articles[] = $data['articles'][$article_url];
        }

        return '';
    }

    private static function filterArticlesBySeries($series_url, $data, $query)
    {
        $articles = [];

        foreach ($data['articles'] as $article) {
            if ($article->series_url === $series_url) {
                $articles[] = $article;
            }
        }

        return $articles;
    }
}
