<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('waiters');

        Schema::table('stores', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->after('name');
            $table->string('address')->nullable()->after('code');
            $table->string('phone', 64)->nullable()->after('address');
            $table->boolean('is_primary_stock_location')->default(true)->after('phone');
            $table->text('notes')->nullable()->after('is_primary_stock_location');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->string('section', 64)->nullable()->after('table_name');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('section');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('can_access_app')->default(false)->after('position_id');
            $table->date('hire_date')->nullable()->after('can_access_app');
            $table->string('employment_status', 32)->default('active')->after('hire_date');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('setting_group', 64)->default('general')->after('key');
        });

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 14, 3)->default(0);
            $table->decimal('reorder_level', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'product_id']);
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('inventory_stocks');
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('setting_group');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'can_access_app', 'hire_date', 'employment_status']);
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['section', 'sort_order']);
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['code', 'address', 'phone', 'is_primary_stock_location', 'notes']);
        });
    }
};
