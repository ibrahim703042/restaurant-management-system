<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RespondsWithDataTables
{
    /**
     * Server-side DataTables JSON. $baseQuery should include fixed filters (status, store_id…).
     *
     * @param  callable(Builder, string): void  $applySearch
     * @param  callable(Builder): void  $applyOrder
     * @param  callable(object): array<string, mixed>  $mapRow
     */
    protected function dataTablesOf(
        Builder $baseQuery,
        Request $request,
        callable $applySearch,
        callable $applyOrder,
        callable $mapRow
    ): JsonResponse {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(10, (int) $request->input('length', 25)));
        $search = trim((string) $request->input('search.value', ''));

        $recordsTotal = (clone $baseQuery)->count();

        $filteredQuery = clone $baseQuery;
        if ($search !== '') {
            $applySearch($filteredQuery, $search);
        }
        $recordsFiltered = (clone $filteredQuery)->count();

        $applyOrder($filteredQuery);

        $rows = (clone $filteredQuery)->skip($start)->take($length)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows->map($mapRow)->values()->all(),
        ]);
    }
}
