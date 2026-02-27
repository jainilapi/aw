<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'symbol' => 'pc'],
            ['name' => 'Unit', 'symbol' => 'unit'],
            ['name' => 'Item', 'symbol' => 'item'],
            ['name' => 'Pair', 'symbol' => 'pair'],
            ['name' => 'Set', 'symbol' => 'set'],

            ['name' => 'Pack', 'symbol' => 'pack'],
            ['name' => 'Packet', 'symbol' => 'pkt'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Case', 'symbol' => 'case'],
            ['name' => 'Carton', 'symbol' => 'ctn'],
            ['name' => 'Master Carton', 'symbol' => 'mctn'],
            ['name' => 'Pallet', 'symbol' => 'plt'],
            ['name' => 'Bundle', 'symbol' => 'bdl'],
            ['name' => 'Roll', 'symbol' => 'roll'],
            ['name' => 'Tray', 'symbol' => 'tray'],
            ['name' => 'Tube', 'symbol' => 'tube'],
            ['name' => 'Can', 'symbol' => 'can'],
            ['name' => 'Bottle', 'symbol' => 'btl'],
            ['name' => 'Jar', 'symbol' => 'jar'],
            ['name' => 'Bag', 'symbol' => 'bag'],
            ['name' => 'Sack', 'symbol' => 'sack'],
            ['name' => 'Drum', 'symbol' => 'drum'],
            ['name' => 'Container', 'symbol' => 'container'],

            ['name' => 'Dozen', 'symbol' => 'doz'],
            ['name' => 'Gross', 'symbol' => 'gross'],

            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Ton', 'symbol' => 'ton'],
            ['name' => 'Pound', 'symbol' => 'lb'],

            ['name' => 'Milliliter', 'symbol' => 'ml'],
            ['name' => 'Liter', 'symbol' => 'l'],
            ['name' => 'Gallon', 'symbol' => 'gal'],
        ];

        DB::table('aw_units')->insert($units);
    }
}
