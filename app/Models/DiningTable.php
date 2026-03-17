<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Restaurant dining table (seating), not a DB "tables" metadata table.
 */
class DiningTable extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'table_name',
        'section',
        'zone_id',
        'sort_order',
        'capacity',
        'status',
        'store_id',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DiningZone::class, 'zone_id');
    }
}
