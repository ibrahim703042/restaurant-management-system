<?php

namespace App\Console\Commands;

use App\Models\InventoryStock;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeduplicateProductsCommand extends Command
{
    protected $signature = 'products:deduplicate {--dry-run : Show groups only}';

    protected $description = 'Merge duplicate products (same category, name, price); point order_items to canonical id; set store_id null';

    public function handle(): int
    {
        $groups = Product::query()
            ->selectRaw('category_id, LOWER(TRIM(product_name)) as n, price, MIN(id) as keep_id, GROUP_CONCAT(id ORDER BY id) as ids')
            ->groupBy('category_id', 'n', 'price')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate product groups found.');

            return self::SUCCESS;
        }

        foreach ($groups as $g) {
            $ids = array_map('intval', explode(',', $g->ids));
            $keep = (int) $g->keep_id;
            $merge = array_values(array_diff($ids, [$keep]));
            $this->line("Keep #{$keep}, merge: ".implode(',', $merge));

            if ($this->option('dry-run')) {
                continue;
            }

            DB::transaction(function () use ($keep, $merge) {
                foreach ($merge as $oldId) {
                    DB::table('order_items')->where('product_id', $oldId)->update(['product_id' => $keep]);
                    $stocks = InventoryStock::query()->where('product_id', $oldId)->get();
                    foreach ($stocks as $s) {
                        $target = InventoryStock::query()->firstOrCreate(
                            ['store_id' => $s->store_id, 'product_id' => $keep],
                            ['quantity' => 0, 'reorder_level' => 0, 'unit_id' => $s->unit_id]
                        );
                        $target->quantity = (float) $target->quantity + (float) $s->quantity;
                        $target->reorder_level = max((float) $target->reorder_level, (float) $s->reorder_level);
                        $target->save();
                        $s->delete();
                    }
                    Product::query()->whereKey($oldId)->delete();
                }
            });
        }

        if (! $this->option('dry-run')) {
            Product::query()->update(['store_id' => null]);
            $this->info('All products store_id set to null (shared catalog).');
        }

        return self::SUCCESS;
    }
}
