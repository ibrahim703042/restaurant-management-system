<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name', 'price', 'description', 'category_id', 'store_id', 'image', 'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeSharedCatalog($query)
    {
        return $query->whereNull('store_id');
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class, 'product_id');
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function primaryImagePath(): ?string
    {
        $m = $this->mediaItems()->where('collection', 'image')->first();
        if ($m && $m->path !== '') {
            return $m->path;
        }
        $legacy = trim((string) ($this->attributes['image'] ?? ''));
        if ($legacy !== '' && $legacy !== 'products/default.png') {
            return $legacy;
        }

        return null;
    }
}
