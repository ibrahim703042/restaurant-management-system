<?php

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    $user->load('stores:id,name,code,status');
    $assigned = $user->stores->where('status', 1)->values();
    $stores = $assigned->isEmpty()
        ? Store::query()->where('status', 1)->orderByDesc('is_primary_stock_location')->orderBy('name')->get(['id', 'name', 'code'])
        : $assigned->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code])->values();

    return [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'stores' => $stores,
        'stores_restricted' => $assigned->isNotEmpty(),
    ];
});
