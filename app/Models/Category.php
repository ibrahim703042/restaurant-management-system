<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    // protected $primarykey ="id";
    protected $fillable = [
        'name',
        'status',
        'image',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /** Path relative to storage/app/public (legacy column or media). */
    public function primaryImagePath(): ?string
    {
        $m = $this->mediaItems()->where('collection', 'image')->first();
        if ($m && $m->path !== '') {
            return $m->path;
        }
        $legacy = trim((string) ($this->attributes['image'] ?? ''));

        return $legacy !== '' ? $legacy : null;
    }
}
