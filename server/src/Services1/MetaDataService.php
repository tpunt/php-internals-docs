<?php

namespace PHPInternalsDocs\Services;

class MetaDataService
{
    public static function performMetaActions($response, $query)
    {
        $resultCount = \count($response);
        $offset = intval($query['offset'] ?? 0);
        $limit = intval($query['limit'] ?? 10);
        $pageCount = intval($resultCount / $limit);
        $currentPage = \intval($offset / $limit) + 1;

        if ($resultCount % $limit) {
            ++$pageCount;
        }

        $response = \array_slice($response, $offset, $limit);

        return [
            'default' => $response,
            'meta' => [
                'total' => $resultCount,
                'offset' => $offset,
                'limit' => $limit,
                'current_page' => $currentPage,
                'page_count' => $pageCount
            ]
        ];
    }
}
