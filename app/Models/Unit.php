<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['code', 'name'];

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'unit_id');
    }
}
