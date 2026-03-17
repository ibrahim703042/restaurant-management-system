<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name', 64);
            $table->timestamps();
        });

        $now = now();
        DB::table('units')->insert([
            ['code' => 'pcs', 'name' => 'Piece', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'kg', 'name' => 'Kilogram', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'L', 'name' => 'Liter', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'portion', 'name' => 'Portion', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $defaultUnitId = 1;

        Schema::create('dining_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 128);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('status');
        });

        Schema::table('inventory_stocks', function (Blueprint $table) use ($defaultUnitId) {
            $table->foreignId('unit_id')->after('product_id')->default($defaultUnitId)->constrained('units')->restrictOnDelete();
        });

        // MySQL: make products.store_id nullable
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
        });
        DB::statement('ALTER TABLE products MODIFY store_id BIGINT UNSIGNED NULL');
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('store_id')->constrained('dining_zones')->nullOnDelete();
        });

        // Migrate section strings to dining_zones per store
        $sections = DB::table('tables')
            ->select('store_id', 'section')
            ->whereNotNull('section')
            ->where('section', '!=', '')
            ->distinct()
            ->get();

        $zoneMap = [];
        foreach ($sections as $row) {
            $key = $row->store_id.'|'.$row->section;
            if (isset($zoneMap[$key])) {
                continue;
            }
            $id = DB::table('dining_zones')->insertGetId([
                'store_id' => $row->store_id,
                'name' => $row->section,
                'sort_order' => 0,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $zoneMap[$key] = $id;
        }

        $tables = DB::table('tables')->whereNotNull('section')->where('section', '!=', '')->get();
        foreach ($tables as $t) {
            $key = $t->store_id.'|'.$t->section;
            if (isset($zoneMap[$key])) {
                DB::table('tables')->where('id', $t->id)->update(['zone_id' => $zoneMap[$key]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
        });
        DB::statement('UPDATE products SET store_id = (SELECT id FROM stores ORDER BY id LIMIT 1) WHERE store_id IS NULL');
        DB::statement('ALTER TABLE products MODIFY store_id BIGINT UNSIGNED NOT NULL');
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::dropIfExists('dining_zones');
        Schema::dropIfExists('units');
    }
};
