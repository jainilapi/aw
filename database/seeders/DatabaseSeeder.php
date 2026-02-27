<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CountriesTableSeeder::class,
            SettingSeeder::class,
            WarehouseSeeder::class,
            CurrencySeeder::class,
            StatesTableSeeder::class,
            // BrandSeeder::class,
            // CategorySeeder::class,
            UnitSeeder::class,
            TaxSeeder::class,
            // ProductSeeder::class,
            HomePageSettingSeeder::class,
            CitiesTableChunkOneSeeder::class,
            CitiesTableChunkTwoSeeder::class,
            CitiesTableChunkThreeSeeder::class,
            CitiesTableChunkFourSeeder::class,
            CitiesTableChunkFiveSeeder::class,
        ]);
    }
}
