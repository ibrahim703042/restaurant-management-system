<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['order_number', 'client_id', 'table_id', 'user_id', 'status', 'total', 'notes'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }
}
