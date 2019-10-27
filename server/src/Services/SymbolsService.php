<?php

namespace PHPInternalsDocs\Services;

use PHPInternalsDocs\Models\Error;

/*
Fetch all symbols:
 - https://phpinternals.net/api/symbols

Fetch one symbol:
 - https://phpinternals.net/api/symbols/27223581

Fetch related symbols (same category):
 - https://phpinternals.net/api/symbols?category=zend_strings&ordering=asc&limit=10

Fetch a symbol by name:
 - https://phpinternals.net/api/symbols?search==zend_string_equals
*/

class SymbolsService
{
    public static function fetchSymbols($data, $query)
    {
        $symbols = [];

        if (isset($query['search'])) {
            if (strlen($query['search']) > 0 && $query['search'][0] === '=') {
                $symbol_url = substr($query['search'], 1);
                $symbols = self::filterSymbolByUrl($data, $symbol_url);
            } else {
                // TODO generic search
            }
        } else {
            if (isset($query['category'])) {
                $symbols = self::filterSymbolsByCategory($data, $query['category']);
            } else {
                $symbols = array_values($data['symbols_compact']);
            }

            $ordering = $query['ordering'] ?? 'asc';

            switch ($ordering) {
                case 'asc':
                case 'desc':
                    usort($symbols, function ($a, $b) use ($ordering) {
                        if ($ordering === 'desc') {
                            return $a->name < $b->name;
                        }

                        return $a->name > $b->name;
                    });
            }
        }

        return ['code' => 200, 'body' => $symbols];
    }

    public static function fetchSymbol($data, $symbol_id)
    {
        if (isset($data['symbols'][$symbol_id])) {
            return ['code' => 200, 'body' => $data['symbols'][$symbol_id]];
        }

        return ['code' => 404, 'body' => new Error('Symbol not found')];
    }

    private static function filterSymbolByUrl($data, $symbol_url)
    {
        $symbols = [];

        if (isset($data['symbols_ids_by_url'][$symbol_url])) {
            foreach ($data['symbols_ids_by_url'][$symbol_url] as $symbol_id) {
                $symbols[] = $data['symbols'][$symbol_id];
            }
        }

        return $symbols;
    }

    private static function filterSymbolsByCategory($data, $category_url)
    {
        $symbols = [];

        if (isset($data['categories'][$category_url])) {
            foreach ($data['categories'][$category_url]->symbols as $symbol_id) {
                $symbols[] = $data['symbols_compact'][$symbol_id];
            }
        }

        return $symbols;
    }
}
