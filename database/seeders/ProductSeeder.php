<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Setup Basic Units
        $unitPcs = DB::table('aw_units')->insertGetId([
            'name' => 'Piece', 'symbol' => 'pcs', 'created_at' => now()
        ]);

        // 2. Setup Brands
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Logitech', 'slug' => 'logitech'],
            ['name' => 'Sony', 'slug' => 'sony'],
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Anker', 'slug' => 'anker'],
        ];
        foreach ($brands as $b) {
            DB::table('aw_brands')->updateOrInsert(['slug' => $b['slug']], $b + ['status' => 1, 'created_at' => now()]);
        }
        $brandIds = DB::table('aw_brands')->pluck('id', 'slug');

        // 3. Setup Categories
        $catElectronics = DB::table('aw_categories')->insertGetId([
            'name' => 'Electronics', 'slug' => 'electronics', 'status' => 1, 'created_at' => now()
        ]);

        // 4. Setup Attributes for Variable Products
        $attrColor = DB::table('aw_attributes')->insertGetId(['name' => 'Color', 'created_at' => now()]);
        $attrStorage = DB::table('aw_attributes')->insertGetId(['name' => 'Storage', 'created_at' => now()]);

        $valBlack = DB::table('aw_attribute_values')->insertGetId(['attribute_id' => $attrColor, 'value' => 'Space Gray']);
        $valSilver = DB::table('aw_attribute_values')->insertGetId(['attribute_id' => $attrColor, 'value' => 'Silver']);
        $val256gb = DB::table('aw_attribute_values')->insertGetId(['attribute_id' => $attrStorage, 'value' => '256GB']);
        $val512gb = DB::table('aw_attribute_values')->insertGetId(['attribute_id' => $attrStorage, 'value' => '512GB']);

        // --- START SEEDING PRODUCTS ---

        // A. SIMPLE PRODUCTS (3)
        $simpleProducts = [
            [
                'name' => 'Logitech MX Master 3S Wireless Mouse',
                'sku' => 'LOGI-MX3S',
                'brand_id' => $brandIds['logitech'],
                'price' => 99.00,
                'img' => 'https://m.media-amazon.com/images/I/61ni3t1ryQL._AC_SL1500_.jpg'
            ],
            [
                'name' => 'Sony WH-1000XM5 Noise Canceling Headphones',
                'sku' => 'SONY-WHXM5',
                'brand_id' => $brandIds['sony'],
                'price' => 398.00,
                'img' => 'https://m.media-amazon.com/images/I/51aXvjzcukL._AC_SL1500_.jpg'
            ],
            [
                'name' => 'Anker 737 Power Bank (PowerCore 24K)',
                'sku' => 'ANK-737-PB',
                'brand_id' => $brandIds['anker'],
                'price' => 149.99,
                'img' => 'https://m.media-amazon.com/images/I/61S6Le5NisL._AC_SL1500_.jpg'
            ],
        ];

        foreach ($simpleProducts as $sp) {
            $pid = DB::table('aw_products')->insertGetId([
                'product_type' => 'simple',
                'name' => $sp['name'],
                'sku' => $sp['sku'],
                'slug' => Str::slug($sp['name']),
                'brand_id' => $sp['brand_id'],
                'status' => 'active',
                'created_at' => now()
            ]);
            $this->addPriceAndImage($pid, null, $unitPcs, $sp['price'], $sp['img']);
        }

        // B. VARIABLE PRODUCTS (4)
        // 1. iPhone 15 Pro
        $iphoneId = DB::table('aw_products')->insertGetId([
            'product_type' => 'variable',
            'name' => 'Apple iPhone 15 Pro',
            'slug' => 'apple-iphone-15-pro',
            'brand_id' => $brandIds['apple'],
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->addVariant($iphoneId, 'iPhone 15 Pro - Black - 256GB', 'IP15P-BLK-256', 999.00, [$valBlack, $val256gb], $unitPcs, 'https://m.media-amazon.com/images/I/41me-QfWqRL._SY300_SX300_QL70_FMwebp_.jpg');
        $this->addVariant($iphoneId, 'iPhone 15 Pro - Silver - 512GB', 'IP15P-SIL-512', 1199.00, [$valSilver, $val512gb], $unitPcs, 'https://m.media-amazon.com/images/I/41me-QfWqRL._SY300_SX300_QL70_FMwebp_.jpg');

        // 2. Samsung Galaxy S24 Ultra
        $s24Id = DB::table('aw_products')->insertGetId([
            'product_type' => 'variable',
            'name' => 'Samsung Galaxy S24 Ultra',
            'slug' => 'samsung-s24-ultra',
            'brand_id' => $brandIds['samsung'],
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->addVariant($s24Id, 'S24 Ultra - Titanium Black', 'S24U-BLK', 1299.00, [$valBlack], $unitPcs, 'https://m.media-amazon.com/images/I/41CDymsLqvL._SY300_SX300_QL70_FMwebp_.jpg');

        // 3. Logitech G Pro X Superlight
        $gproId = DB::table('aw_products')->insertGetId([
            'product_type' => 'variable',
            'name' => 'Logitech G Pro X Superlight 2',
            'slug' => 'logitech-g-pro-x-2',
            'brand_id' => $brandIds['logitech'],
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->addVariant($gproId, 'G Pro X 2 - Black', 'GPRO2-BLK', 159.00, [$valBlack], $unitPcs, 'https://m.media-amazon.com/images/I/61ocaaw5KRL._AC_UY218_.jpg');

        // 4. Samsung T7 Shield SSD
        $ssdId = DB::table('aw_products')->insertGetId([
            'product_type' => 'variable',
            'name' => 'Samsung T7 Shield Portable SSD',
            'slug' => 'samsung-t7-shield',
            'brand_id' => $brandIds['samsung'],
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->addVariant($ssdId, 'T7 Shield - 1TB Black', 'T7-1TB-BLK', 99.99, [$valBlack], $unitPcs, 'https://m.media-amazon.com/images/I/810RobAS7FL._AC_UY218_.jpg');


        // C. BUNDLE PRODUCTS (3)
        // 1. Ultimate Creator Bundle (Mouse + Headphone)
        $bundle1 = DB::table('aw_products')->insertGetId([
            'product_type' => 'bundle',
            'name' => 'Ultimate Workstation Bundle',
            'slug' => 'ultimate-workstation-bundle',
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->createBundle($bundle1, 450.00, [
            ['product_id' => 1, 'qty' => 1], // MX Master
            ['product_id' => 2, 'qty' => 1], // Sony Headphones
        ]);

        // 2. Apple Essentials Bundle (iPhone + Powerbank)
        $bundle2 = DB::table('aw_products')->insertGetId([
            'product_type' => 'bundle',
            'name' => 'Traveler Power Bundle',
            'slug' => 'traveler-power-bundle',
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->createBundle($bundle2, 1100.00, [
            ['product_id' => 4, 'variant_id' => 1, 'qty' => 1], // iPhone Variant
            ['product_id' => 3, 'qty' => 1], // Anker Powerbank
        ]);

        // 3. Samsung Mobile Pro Kit
        $bundle3 = DB::table('aw_products')->insertGetId([
            'product_type' => 'bundle',
            'name' => 'Samsung Pro Power Kit',
            'slug' => 'samsung-pro-kit',
            'status' => 'active',
            'created_at' => now()
        ]);
        $this->createBundle($bundle3, 1350.00, [
            ['product_id' => 5, 'variant_id' => 3, 'qty' => 1], // S24 Ultra
            ['product_id' => 7, 'variant_id' => 5, 'qty' => 1], // T7 SSD
        ]);
    }

    // Helper to handle pricing and image assignment
    private function addPriceAndImage($productId, $variantId, $unitId, $price, $imgPath) {
        DB::table('aw_prices')->insert([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'unit_id' => $unitId,
            'pricing_type' => 'fixed',
            'base_price' => $price,
            'created_at' => now()
        ]);

        DB::table('aw_product_images')->insert([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'image_path' => $imgPath,
            'position' => 0,
            'created_at' => now()
        ]);

        DB::table('aw_product_units')->insert([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'unit_id' => $unitId,
            'conversion_factor' => 1,
            'is_base' => 1,
            'is_default_selling' => 1,
            'created_at' => now()
        ]);
    }

    // Helper for Variants
    private function addVariant($productId, $name, $sku, $price, $attrValueIds, $unitId, $img) {
        $vid = DB::table('aw_product_variants')->insertGetId([
            'product_id' => $productId,
            'name' => $name,
            'sku' => $sku,
            'status' => 'active',
            'created_at' => now()
        ]);

        foreach ($attrValueIds as $avid) {
            DB::table('aw_variant_attribute_values')->insert([
                'variant_id' => $vid,
                'attribute_value_id' => $avid,
                'created_at' => now()
            ]);
        }

        $this->addPriceAndImage($productId, $vid, $unitId, $price, $img);
        return $vid;
    }

    // Helper for Bundles
    private function createBundle($productId, $fixedPrice, $items) {
        $bundleId = DB::table('aw_bundles')->insertGetId([
            'product_id' => $productId,
            'pricing_mode' => 'fixed',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'created_at' => now()
        ]);

        foreach ($items as $item) {
            DB::table('aw_bundle_items')->insert([
                'bundle_id' => $bundleId,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['qty'],
                'unit_id' => 1, // Assume Piece
                'created_at' => now()
            ]);
        }

        // Add bundle price to price table
        DB::table('aw_prices')->insert([
            'product_id' => $productId,
            'unit_id' => 1,
            'pricing_type' => 'fixed',
            'base_price' => $fixedPrice,
            'created_at' => now()
        ]);
    }
}