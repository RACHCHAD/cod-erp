<?php

namespace Database\Seeders;

use App\Models\AdAccount;
use App\Models\AdSpend;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Platform;
use App\Models\Product;
use App\Models\SourcingRequest;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@cod-erp.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin']);

        $manager = User::updateOrCreate(
            ['email' => 'manager@cod-erp.test'],
            ['name' => 'Sara Manager', 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
        );
        $manager->syncRoles(['manager']);

        $buyer = User::updateOrCreate(
            ['email' => 'buyer@cod-erp.test'],
            ['name' => 'Yassin Buyer', 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
        );
        $buyer->syncRoles(['media_buyer']);

        $agent = User::updateOrCreate(
            ['email' => 'agent@cod-erp.test'],
            ['name' => 'Aïcha Agent', 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
        );
        $agent->syncRoles(['agent']);

        $sourcer = User::updateOrCreate(
            ['email' => 'sourcer@cod-erp.test'],
            ['name' => 'Karim Sourcer', 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
        );
        $sourcer->syncRoles(['sourcing_manager']);

        $accountant = User::updateOrCreate(
            ['email' => 'accountant@cod-erp.test'],
            ['name' => 'Nadia Accountant', 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
        );
        $accountant->syncRoles(['accountant']);

        $teamBuyers = Team::updateOrCreate(['slug' => 'media-buyers'], [
            'name' => 'Media Buyers', 'color' => '#6366f1', 'description' => 'Performance media buying team',
        ]);
        $teamAgents = Team::updateOrCreate(['slug' => 'call-center'], [
            'name' => 'Call Center', 'color' => '#10b981', 'description' => 'Agents handling lead confirmation',
        ]);
        $teamBuyers->members()->syncWithoutDetaching([$buyer->id => ['role' => 'lead']]);
        $teamAgents->members()->syncWithoutDetaching([$agent->id => ['role' => 'agent']]);

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Shenzhen Quanmai Tech'],
            ['website' => 'https://alibaba.com', 'contact_name' => 'May Liu', 'contact_email' => 'may@quanmai.cn', 'country_code' => 'CN'],
        );

        $products = collect([
            ['Slim Watch X1', 'SKU-WATCH-X1', 7.50, 39.90, 4.20, 'winning'],
            ['Neck Massager Pro', 'SKU-NECK-PRO', 9.00, 49.90, 5.00, 'scaling'],
            ['Smart Lash Curler', 'SKU-LASH-CRL', 4.20, 24.90, 3.50, 'testing'],
            ['Posture Corrector', 'SKU-POS-CRX', 3.80, 29.90, 4.00, 'winning'],
            ['Wireless Earbuds Z', 'SKU-EBD-Z',  6.20, 34.90, 4.50, 'scaling'],
        ])->map(function ($p) use ($supplier) {
            return Product::updateOrCreate(['sku' => $p[1]], [
                'name' => $p[0], 'cost' => $p[2], 'selling_price' => $p[3],
                'avg_delivery_cost' => $p[4], 'status' => $p[5], 'supplier_id' => $supplier->id,
                'tags' => ['demo'],
            ]);
        });

        $warehouseMa = Warehouse::firstOrCreate(
            ['name' => 'Casablanca Main'],
            ['country_id' => Country::where('code', 'MA')->value('id'), 'city' => 'Casablanca', 'active' => true],
        );
        $warehouseSn = Warehouse::firstOrCreate(
            ['name' => 'Dakar Hub'],
            ['country_id' => Country::where('code', 'SN')->value('id'), 'city' => 'Dakar', 'active' => true],
        );

        foreach ($products as $product) {
            foreach ([$warehouseMa, $warehouseSn] as $wh) {
                $stock = Stock::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $wh->id],
                    ['quantity' => rand(40, 400), 'low_stock_threshold' => 20],
                );
                StockMovement::create([
                    'stock_id' => $stock->id, 'type' => 'in',
                    'quantity' => $stock->quantity, 'user_id' => $admin->id,
                    'notes' => 'Initial stock',
                ]);
            }
        }

        foreach (Platform::all() as $platform) {
            AdAccount::firstOrCreate(
                ['name' => $platform->name.' Main Account'],
                ['platform_id' => $platform->id, 'external_id' => 'EXT-'.strtoupper($platform->slug).'-001',
                    'currency_id' => Currency::where('code', 'USD')->value('id'),
                    'daily_limit' => 500, 'status' => 'active'],
            );
        }

        $countries = Country::whereIn('code', ['MA', 'SN', 'CI', 'CM', 'NG'])->get();
        $platforms = Platform::whereIn('slug', ['facebook', 'tiktok', 'google', 'snapchat'])->get();

        foreach (range(0, 29) as $offset) {
            $date = now()->subDays($offset)->toDateString();
            foreach ($products as $product) {
                foreach ($countries as $country) {
                    foreach ($platforms as $platform) {
                        $spend = round(rand(5, 80) + (rand(0, 99) / 100), 2);
                        $leads = max(1, (int) round($spend / max(0.5, rand(1, 7))));
                        $account = AdAccount::where('platform_id', $platform->id)->first();
                        AdSpend::create([
                            'date' => $date,
                            'product_id' => $product->id,
                            'country_id' => $country->id,
                            'platform_id' => $platform->id,
                            'ad_account_id' => $account?->id,
                            'user_id' => $buyer->id,
                            'spend' => $spend,
                            'leads' => $leads,
                            'impressions' => $leads * rand(50, 250),
                            'clicks' => $leads * rand(3, 15),
                        ]);
                    }
                }
            }
        }

        $statuses = ['new', 'contacted', 'confirmed', 'unreachable', 'cancelled'];
        foreach (range(1, 80) as $i) {
            $product = $products->random();
            $country = $countries->random();
            $lead = Lead::create([
                'product_id' => $product->id,
                'country_id' => $country->id,
                'platform_id' => $platforms->random()->id,
                'agent_id' => $agent->id,
                'customer_name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'city' => fake()->city(),
                'address' => fake()->streetAddress(),
                'status' => $statuses[array_rand($statuses)],
                'contacted_at' => now()->subHours(rand(0, 200)),
            ]);

            if ($lead->status === 'confirmed') {
                $order = Order::create([
                    'lead_id' => $lead->id,
                    'country_id' => $lead->country_id,
                    'agent_id' => $agent->id,
                    'warehouse_id' => $warehouseMa->id,
                    'status' => collect(['confirmed', 'shipped', 'delivered', 'delivered', 'returned'])->random(),
                    'delivery_cost' => $product->avg_delivery_cost,
                    'confirmed_at' => now()->subDays(rand(0, 10)),
                ]);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 2),
                    'unit_price' => $product->selling_price,
                    'unit_cost' => $product->cost,
                ]);
                $order->recalculate();
                if ($order->status === 'delivered') {
                    $order->delivered_at = now()->subDays(rand(0, 5));
                }
                $order->save();
            }
        }

        $catSourcing = ExpenseCategory::where('slug', 'suppliers')->first();
        $catSalaries = ExpenseCategory::where('slug', 'salaries')->first();
        $catRent = ExpenseCategory::where('slug', 'rent')->first();
        $catSoft = ExpenseCategory::where('slug', 'software')->first();

        Expense::create(['category_id' => $catSalaries->id, 'user_id' => $admin->id, 'date' => now()->startOfMonth(),
            'amount' => 2400, 'title' => 'Team payroll – this month', 'is_recurring' => true, 'recurring_interval' => 'monthly']);
        Expense::create(['category_id' => $catRent->id, 'user_id' => $admin->id, 'date' => now()->startOfMonth(),
            'amount' => 600, 'title' => 'Office rent', 'is_recurring' => true, 'recurring_interval' => 'monthly']);
        Expense::create(['category_id' => $catSoft->id, 'user_id' => $admin->id, 'date' => now()->subDays(5),
            'amount' => 89, 'title' => 'CRM & tooling stack']);
        Expense::create(['category_id' => $catSourcing->id, 'user_id' => $admin->id, 'date' => now()->subDays(2),
            'amount' => 540, 'title' => 'Supplier deposit batch']);

        SourcingRequest::create([
            'product_name' => 'Foldable Stroller Lite',
            'supplier_link' => 'https://alibaba.com/example-foldable',
            'supplier_id' => $supplier->id,
            'product_cost' => 18.5,
            'shipping_cost' => 4.2,
            'estimated_selling_price' => 79.9,
            'target_countries' => ['MA', 'SN', 'CI'],
            'status' => 'reviewing',
            'requested_by' => $sourcer->id,
        ]);
        SourcingRequest::create([
            'product_name' => 'Mini Air Humidifier',
            'product_cost' => 3.6,
            'shipping_cost' => 1.4,
            'estimated_selling_price' => 19.9,
            'target_countries' => ['CM', 'CI', 'BJ'],
            'status' => 'approved',
            'requested_by' => $sourcer->id,
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(2),
        ]);
    }
}
