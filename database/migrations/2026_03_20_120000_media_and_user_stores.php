<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('collection', 64)->default('image');
            $table->string('disk', 32)->default('public');
            $table->string('path', 512);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('store_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'store_id']);
        });

        // Backfill media from existing category / product image paths
        foreach (DB::table('categories')->whereNotNull('image')->where('image', '!=', '')->get() as $row) {
            DB::table('media')->insert([
                'mediable_type' => 'App\\Models\\Category',
                'mediable_id' => $row->id,
                'collection' => 'image',
                'disk' => 'public',
                'path' => $row->image,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach (DB::table('products')->whereNotNull('image')->where('image', '!=', '')->where('image', '!=', 'products/default.png')->get() as $row) {
            DB::table('media')->insert([
                'mediable_type' => 'App\\Models\\Product',
                'mediable_id' => $row->id,
                'collection' => 'image',
                'disk' => 'public',
                'path' => $row->image,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $defaultPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = storage_path('app/public/default.png');
        if (! is_file($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $defaultPng);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_user');
        Schema::dropIfExists('media');
    }
};
