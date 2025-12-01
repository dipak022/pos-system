<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create products with different offer scenarios

        // 1. Product with active discount
        Product::create([
            'name' => 'Laptop',
            'price' => 1000.00,
            'stock' => 50,
            'discount' => 10.00, // 10% discount
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(5),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(30),
        ]);

        // 2. Product with active trade offer (Buy 3 Get 1 Free)
        Product::create([
            'name' => 'Mouse',
            'price' => 25.00,
            'stock' => 100,
            'trade_offer_min_qty' => 3,
            'trade_offer_get_qty' => 1,
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(10),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(20),
        ]);

        // 3. Product with active trade offer (Buy 5 Get 2 Free)
        Product::create([
            'name' => 'Keyboard',
            'price' => 50.00,
            'stock' => 75,
            'trade_offer_min_qty' => 5,
            'trade_offer_get_qty' => 2,
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(3),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(15),
        ]);

        // 4. Product with no offer
        Product::create([
            'name' => 'Monitor',
            'price' => 300.00,
            'stock' => 30,
        ]);

        // 5. Product with expired discount
        Product::create([
            'name' => 'Webcam',
            'price' => 75.00,
            'stock' => 40,
            'discount' => 15.00,
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(30),
            'discount_or_trade_offer_end_date' => Carbon::now()->subDays(5),
        ]);

        // 6. Product with future discount
        Product::create([
            'name' => 'Headphones',
            'price' => 100.00,
            'stock' => 60,
            'discount' => 20.00,
            'discount_or_trade_offer_start_date' => Carbon::now()->addDays(5),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(30),
        ]);

        // 7. Product with high discount
        Product::create([
            'name' => 'USB Cable',
            'price' => 10.00,
            'stock' => 200,
            'discount' => 25.00, // 25% discount
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(2),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(10),
        ]);

        // 8. Product with trade offer (Buy 2 Get 1 Free)
        Product::create([
            'name' => 'Phone Case',
            'price' => 15.00,
            'stock' => 150,
            'trade_offer_min_qty' => 2,
            'trade_offer_get_qty' => 1,
            'discount_or_trade_offer_start_date' => Carbon::now()->subDays(1),
            'discount_or_trade_offer_end_date' => Carbon::now()->addDays(25),
        ]);
    }
}
