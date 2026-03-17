<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_active', 'setting_group'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function isModuleActive(string $key): bool
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting ? (bool) $setting->is_active : true;
    }

    public static function forgetCache(): void
    {
        Cache::forget('app_settings_modules');
    }
}
