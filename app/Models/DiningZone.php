<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Floor zone per store; tables link via {@see DiningTable::$zone_id}. */
class DiningZone extends Model
{
    protected $fillable = ['store_id', 'name', 'sort_order', 'status'];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function diningTables(): HasMany
    {
        return $this->hasMany(DiningTable::class, 'zone_id');
    }
}
