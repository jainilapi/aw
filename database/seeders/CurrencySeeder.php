<?php

namespace Database\Seeders;

use App\Models\AwCurrency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'iso_code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.000000,
                'is_base' => true,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 1,
            ],[
                'name' => 'East Caribbean Dollar',
                'iso_code' => 'XCD',
                'symbol' => 'EC$',
                'exchange_rate' => 0.37,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 1,
            ],
            [
                'name' => 'Euro',
                'iso_code' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 0.920000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 2,
            ],
            [
                'name' => 'British Pound',
                'iso_code' => 'GBP',
                'symbol' => '£',
                'exchange_rate' => 0.790000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 3,
            ],
            [
                'name' => 'Indian Rupee',
                'iso_code' => 'INR',
                'symbol' => '₹',
                'exchange_rate' => 83.500000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 4,
            ],
            [
                'name' => 'Canadian Dollar',
                'iso_code' => 'CAD',
                'symbol' => 'CA$',
                'exchange_rate' => 1.360000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 5,
            ],
            [
                'name' => 'Australian Dollar',
                'iso_code' => 'AUD',
                'symbol' => 'A$',
                'exchange_rate' => 1.530000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 6,
            ],
            [
                'name' => 'Japanese Yen',
                'iso_code' => 'JPY',
                'symbol' => '¥',
                'exchange_rate' => 149.500000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 0,
                'symbol_position' => 'before',
                'sort_order' => 7,
            ],
            [
                'name' => 'UAE Dirham',
                'iso_code' => 'AED',
                'symbol' => 'د.إ',
                'exchange_rate' => 3.670000,
                'is_base' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'symbol_position' => 'before',
                'sort_order' => 8,
            ],
        ];

        foreach ($currencies as $currency) {
            AwCurrency::updateOrCreate(
                ['iso_code' => $currency['iso_code']],
                $currency
            );
        }

        $this->command->info('Currencies seeded successfully!');
    }
}
