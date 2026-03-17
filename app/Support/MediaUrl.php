<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

/**
 * Public URL for stored images; falls back to storage/default.png.
 */
final class MediaUrl
{
    public const FALLBACK = 'default.png';

    public static function url(?string $relativePath): string
    {
        $path = $relativePath ? trim($relativePath, '/') : '';
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return asset('storage/'.self::FALLBACK);
    }

    public static function forCategory(Category $category): string
    {
        $path = $category->primaryImagePath();

        return self::url($path);
    }

    public static function forProduct(Product $product): string
    {
        $path = $product->primaryImagePath();

        return self::url($path);
    }
}
