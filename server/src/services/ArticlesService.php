<?php

namespace PHPInternalsDocs\Services;

/*
Fetch all articles:
 - https://phpinternals.net/api/articles?limit=10&ordering=desc&offset=0

Fetch one article:
 - https://phpinternals.net/api/articles/optimising_internal_functions_via_new_opcode_instructions

Fetch articles for a category (related articles):
  - https://phpinternals.net/api/articles?category=opcodes&ordering=desc

Fetch an article series (same link as an article link):
  - https://phpinternals.net/api/articles/php_range_operator
*/

class ArticlesService
{
    public static function fetchArticles($data, $query)
    {
        $articles = [];

        if (isset($query['category'])) {
            $articles = self::filterArticlesByCategory($data, $query['category']);
        } else {
            $articles = array_values($data['articles_compact']);
        }

        if (isset($query['ordering'])) {
            $ordering = $query['ordering'];

            switch ($ordering) {
                case 'asc':
                case 'desc':
                    usort($articles, function ($a, $b) use ($ordering) {
                        if ($ordering === 'desc') {
                            return $a->date < $b->date;
                        }

                        return $a->date > $b->date;
                    });
            }
        }

        return ['code' => 200, 'body' => $articles];
    }

    public static function fetchArticle($data, $article_url, $query)
    {
        $series = self::filterArticlesBySeries($data, $article_url);

        if ($series) {
            return ['code' => 200, 'body' => $series];
        }

        if (isset($data['articles'][$article_url])) {
            return ['code' => 200, 'body' => $data['articles'][$article_url]];
        }

        return ['code' => 404, 'body' => 'Article not found'];
    }

    private static function filterArticlesByCategory($data, $category_url)
    {
        $articles = [];

        if (isset($data['categories'][$category_url])) {
            foreach ($data['categories'][$category_url]->articles as $article_url) {
                $articles[] = $data['articles_compact'][$article_url];
            }
        }

        return $articles;
    }

    private static function filterArticlesBySeries($data, $article_url)
    {
        $articles = [];

        foreach ($data['articles_compact'] as $article) {
            if ($article->series_url === $article_url) {
                $articles[] = $article;
            }
        }

        return $articles;
    }
}
