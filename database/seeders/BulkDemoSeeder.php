<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Client;
use App\Models\DiningTable;
use App\Models\DiningZone;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Large demo dataset (~100+ rows) for dev/staging: shared catalog, multi-store stock, zones, sample bills.
 * Safe to run once; skips if stores with code DEM-01 already exist.
 *
 * Run: php artisan db:seed --class=BulkDemoSeeder
 */
class BulkDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Store::query()->where('code', 'DEM-01')->exists()) {
            $this->command?->info('BulkDemoSeeder skipped (DEM-01 store already exists).');

            return;
        }

        $user = User::query()->first();
        if (! $user) {
            $this->command?->error('No user found. Run AdminUserSeeder first.');

            return;
        }

        $unitId = (int) DB::table('units')->where('code', 'pcs')->value('id') ?: 1;

        DB::transaction(function () use ($user, $unitId) {
            $categoryDefs = [
                ['BD Starters', 'categories/demo-starters.svg'],
                ['BD Soups', ''],
                ['BD Salads', 'categories/demo-salads.svg'],
                ['BD Grill', ''],
                ['BD Seafood', ''],
                ['BD Pasta', 'categories/demo-pasta.svg'],
                ['BD Pizza', ''],
                ['BD Burgers', ''],
                ['BD Sides', ''],
                ['BD Desserts', 'categories/demo-desserts.svg'],
                ['BD Beverages', ''],
                ['BD Coffee', ''],
            ];

            $categories = [];
            foreach ($categoryDefs as [$name, $image]) {
                $categories[] = Category::query()->create([
                    'name' => $name,
                    'status' => 1,
                    'image' => $image ?: '',
                ]);
            }

            $productNames = [
                'Spring rolls', 'Bruschetta', 'Chicken wings', 'Garlic bread', 'Soup of day',
                'Tomato basil soup', 'Minestrone', 'Greek salad', 'Caesar salad', 'Cobb salad',
                'Grilled ribeye', 'Lamb chops', 'BBQ ribs', 'Grilled salmon', 'Fish tacos',
                'Shrimp scampi', 'Lobster tail', 'Seafood platter', 'Spaghetti carbonara', 'Penne arrabiata',
                'Lasagna', 'Margherita pizza', 'Pepperoni pizza', 'Veggie supreme', 'BBQ chicken pizza',
                'Classic burger', 'Cheeseburger', 'Veggie burger', 'French fries', 'Onion rings',
                'Mashed potatoes', 'Coleslaw', 'Chocolate cake', 'Ice cream sundae', 'Cheesecake',
                'Fresh juice', 'Soda', 'Mineral water', 'House wine glass', 'Beer draft',
                'Espresso', 'Cappuccino', 'Latte', 'Green tea', 'Herbal tea',
                'Club sandwich', 'Caesar wrap', 'Chicken curry', 'Beef stir fry', 'Veg pad thai',
                'Mango lassi', 'Smoothie bowl', 'Breakfast combo', 'Pancakes', 'Waffle stack',
            ];

            $products = [];
            $pi = 0;
            foreach ($productNames as $pname) {
                $cat = $categories[$pi % count($categories)];
                $price = 1500 + ($pi * 173 % 12000);
                $products[] = Product::query()->create([
                    'product_name' => $pname,
                    'price' => $price,
                    'description' => 'Demo item',
                    'category_id' => $cat->id,
                    'store_id' => null,
                    'image' => '',
                    'status' => 1,
                ]);
                $pi++;
            }

            $stores = [];
            foreach (['Downtown hub', 'Mall branch', 'Airport kiosk', 'Suburb outlet', 'Harbor view'] as $i => $label) {
                $stores[] = Store::query()->create([
                    'name' => 'Demo '.$label,
                    'code' => 'DEM-0'.($i + 1),
                    'address' => 'Demo address '.($i + 1),
                    'phone' => '+1000000000'.$i,
                    'is_primary_stock_location' => $i === 0,
                    'notes' => 'Bulk demo store',
                    'status' => 1,
                ]);
            }

            $zoneTemplates = [['Main floor', 'Terrace'], ['Indoor', 'Patio'], ['Counter', 'Seating']];
            $zonesByStore = [];
            foreach ($stores as $si => $store) {
                $zonesByStore[$store->id] = [];
                foreach ($zoneTemplates[$si % 3] as $zi => $zname) {
                    $zonesByStore[$store->id][] = DiningZone::query()->create([
                        'store_id' => $store->id,
                        'name' => $zname,
                        'sort_order' => $zi,
                        'status' => 1,
                    ]);
                }
            }

            $tableNo = 1;
            foreach ($stores as $store) {
                $zones = $zonesByStore[$store->id];
                foreach ($zones as $zone) {
                    for ($t = 0; $t < 2; $t++) {
                        DiningTable::query()->create([
                            'table_name' => 'T'.$tableNo,
                            'section' => $zone->name,
                            'zone_id' => $zone->id,
                            'sort_order' => $tableNo,
                            'capacity' => 2 + ($tableNo % 5),
                            'status' => 1,
                            'store_id' => $store->id,
                        ]);
                        $tableNo++;
                    }
                }
            }

            foreach ($stores as $store) {
                foreach ($products as $product) {
                    InventoryStock::query()->firstOrCreate(
                        ['store_id' => $store->id, 'product_id' => $product->id],
                        [
                            'unit_id' => $unitId,
                            'quantity' => random_int(0, 400) + ($product->id % 50),
                            'reorder_level' => 15,
                        ]
                    );
                }
            }

            for ($c = 1; $c <= 12; $c++) {
                Client::query()->create([
                    'name' => 'Demo Client '.$c,
                    'phone' => '+1555000'.str_pad((string) $c, 4, '0', STR_PAD_LEFT),
                    'address' => null,
                    'debt_balance' => 0,
                ]);
            }

            $clients = Client::query()->where('name', 'like', 'Demo Client%')->get();

            for ($o = 0; $o < 10; $o++) {
                $lines = random_int(2, 4);
                $items = [];
                $total = 0;
                for ($l = 0; $l < $lines; $l++) {
                    $product = $products[array_rand($products)];
                    $qty = random_int(1, 3);
                    $unit = (float) $product->price;
                    $line = round($unit * $qty, 2);
                    $total += $line;
                    $items[] = [$product, $qty, $unit, $line];
                }

                $order = Order::query()->create([
                    'order_number' => 'BD-'.Str::upper(Str::random(10)),
                    'client_id' => $clients->random()->id,
                    'user_id' => $user->id,
                    'status' => 'completed',
                    'total' => $total,
                    'notes' => 'Bulk demo order',
                ]);

                foreach ($items as [$product, $qty, $unit, $line]) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'line_total' => $line,
                    ]);
                }

                Bill::query()->create([
                    'bill_number' => 'BL-'.Str::upper(Str::random(8)),
                    'order_id' => $order->id,
                    'qr_token' => Str::random(40),
                    'total' => $total,
                    'payment_status' => $o % 3 === 0 ? 'paid' : ($o % 3 === 1 ? 'partial' : 'unpaid'),
                ]);
            }

            $this->command?->info('BulkDemoSeeder: '.count($categories).' categories, '.count($products).' products, '.count($stores).' stores, '.($tableNo - 1).' tables, inventory rows, 12 clients, 10 orders/bills.');
        });
    }
}
