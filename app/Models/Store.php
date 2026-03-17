<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Branch / location. Stock for menu products is tracked per store via {@see InventoryStock}.
 */
class Store extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'is_primary_stock_location',
        'notes',
        'status',
    ];

    protected $casts = [
        'is_primary_stock_location' => 'boolean',
        'status' => 'integer',
    ];

    public function diningTables(): HasMany
    {
        return $this->hasMany(DiningTable::class, 'store_id');
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'store_id');
    }
}
