<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    protected $table = 'inventory_stocks';

    protected $fillable = [
        'store_id',
        'product_id',
        'unit_id',
        'quantity',
        'reorder_level',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reorder_level' => 'decimal:3',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
