<?php

namespace App\Imports;

use App\Models\{User, AwProduct, AwBrand, AwCategory, AwProductUnit, AwUnit, AwPrice, AwProductCategory};
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimpleProduct implements ToCollection, WithStartRow
{
    protected $importId;

    public function __construct($importId)
    {
        $this->importId = $importId;
    }

    public function collection(Collection $rows)
    {
        $currentProduct = null;
        $isFirstUnitForThisProduct = false;

        foreach ($rows as $row) {
            $sku = trim($row[0] ?? '');
            $name = trim($row[2] ?? '');
            $unitName = trim($row[10] ?? '');
            $conversionQty = floatval($row[12] ?? 1);
            $price = $row[16] ?? null;

            if (!empty($sku) || !empty($name)) {
                $currentProduct = AwProduct::updateOrCreate(
                    ['sku' => $sku ?: 'AUTO-' . Str::random(8)],
                    [
                        'name' => $name,
                        'product_type' => 'simple',
                        'slug' => Str::slug($name ?: $sku) . '-' . Str::random(6),
                        'status' => 'active',
                        'in_stock' => 1
                    ]
                );

                $this->processMetaData($currentProduct, $row);

                $isFirstUnitForThisProduct = true;
            }

            if (!$currentProduct) continue;

            if (!empty($unitName)) {
                $unit = AwUnit::firstOrCreate(['name' => $unitName]);
                $unitId = null;
                $unitType = 0;

                if ($isFirstUnitForThisProduct) {
                    $pbu = AwProductUnit::updateOrCreate(
                        ['product_id' => $currentProduct->id, 'unit_id' => $unit->id, 'is_base' => 1],
                        ['quantity' => 1, 'is_default_selling' => 1]
                    );

                    $unitId = $pbu->id;
                    $unitType = 0;
                    $isFirstUnitForThisProduct = false;
                } else {
                    $pau = AwProductUnit::updateOrCreate(
                        ['product_id' => $currentProduct->id, 'unit_id' => $unit->id, 'is_base' => 0],
                        ['quantity' => $conversionQty]
                    );

                    $unitId = $pau->id;
                    $unitType = 1;
                }

                if ($price !== null && $price !== '') {
                    AwPrice::create([
                        'product_id' => $currentProduct->id,
                        'original_unit_id' => $unit->id,
                        'unit_id' => $unitId,
                        'pricing_type' => 'fixed',
                        'base_price' => $price
                    ]);
                }
            }
        }

        \App\Models\ProductImport::find($this->importId)->update([
            'status' => 'imported',
            'total_rows' => count($rows)
        ]);
    }

    private function processMetaData($product, $row)
    {
        $catBrand = $row[6] ?? '';

        if ($catBrand) {
            $parts = explode('/', $catBrand);
            $categoryName = trim($parts[0] ?? '');
            $brandName = trim($parts[1] ?? '');

            if ($categoryName) {
                $category = AwCategory::firstOrCreate([
                    'slug' => Str::slug($categoryName)
                ], [
                    'name' => $categoryName
                ]);

                AwProductCategory::updateOrCreate([
                    'product_id' => $product->id,
                    'category_id' => $category->id
                ], ['is_primary' => 1]);
            }

            if ($brandName) {
                $brand = AwBrand::firstOrCreate(                    
                    ['slug' => Str::slug($brandName)],
                    ['name' => $brandName]
                );

                AwProduct::where('id', $product->id)->update([
                    'brand_id' => $brand->id
                ]);
            }
        }

        $vendorName = trim($row[8] ?? '');
        if ($vendorName) {
            $supplier = User::where('name', $vendorName)->first();
            if (!$supplier) {
                $supplier = User::create([
                    'name' => $vendorName,
                    'email' => Str::slug($vendorName) . '@anjowholesale.com',
                    'password' => Hash::make(12345678),
                    'status' => 1
                ]);
            }

            if (method_exists($supplier, 'assignRole')) {
                $supplier->assignRole('supplier');
            }
        }
    }

    public function startRow(): int
    {
        return 17;
    }
}