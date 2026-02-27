<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            [
                'name' => 'Standard Rate',
                'tax_percentage' => 17.5
            ],
            [
                'name' => 'Luxury Rate',
                'tax_percentage' => 28
            ],
            [
                'name' => 'Miscellaneous Rate',
                'tax_percentage' => 44
            ]
        ] as $slab) {
            \App\Models\TaxSlab::createOrFirst([
                'tax_percentage' => $slab['tax_percentage']
            ], $slab);
        }
    }
}
