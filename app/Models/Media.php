<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'path',
        'sort_order',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
