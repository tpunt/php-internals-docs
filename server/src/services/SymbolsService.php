<?php

namespace PHPInternalsDocs\Services;

/*
Fetch all symbols:
 - https://phpinternals.net/api/symbols
*/

class SymbolsService
{
    public static function fetchSymbols($data, $query)
    {
        $symbols = array_values($data['symbols_compact']);

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

        return ['code' => 200, 'body' => $symbols];
    }
}
