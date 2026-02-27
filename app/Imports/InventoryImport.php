<?php

namespace App\Imports;

use App\Models\AwProduct;
use App\Models\AwSupplierWarehouseProduct;
use App\Models\AwInventoryMovement;
use App\Models\AwWarehouse;
use App\Models\AwCategory;
use App\Models\AwUnit;
use App\Models\AwProductCategory;
use App\Models\User;
use App\Models\AwProductUnit;
use App\Models\AwPrice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class InventoryImport implements ToCollection, WithStartRow
{
    /**
     * Start reading from row 22 (where the headers are)
     */
    public function startRow(): int
    {
        return 22;
    }

    public function collection(Collection $rows)
    {
        $currentProduct = null;
        $currentSupplier = null;
        $currentUnit = null;

        foreach ($rows as $row) {
            // Mapping based on your image:
            // Col 0 (A) = Item #
            // Col 1 (B) = Description (Header) OR Qty (Location row)
            // Col 2 (C) = Location Name
            // Col 4 (E) = Unit
            // Col 6 (G) = Category
            
            $colA = trim($row[0] ?? '');
            $colB = trim($row[1] ?? '');
            $colC = trim($row[2] ?? '');
            $colE = trim($row[4] ?? '');
            $colG = trim($row[6] ?? '');
            $colJ = trim($row[9] ?? '');

            // 1. SKIP BLANK ROWS
            if (empty($colA) && empty($colB) && empty($colC) && empty($colJ)) {
                continue;
            }

            // 2. IDENTIFY PRODUCT HEADER ROW
            // Condition: Column A has the SKU and it's NOT a "totals" row.
            if (!empty($colA) && !str_contains(strtolower($colA), 'totals') && $colA !== 'Item #') {
                $categoryName = $colG;
                $categoryId = null;

                if (!empty($categoryName)) {
                    $category = AwCategory::firstOrCreate(
                        ['name' => $categoryName],
                        [
                            'status' => 1,
                            'slug' => \Illuminate\Support\Str::slug($categoryName)
                        ]
                    );
                    $categoryId = $category->id;
                }

                $productName = !empty($colB) ? $colB : 'Imported Product - ' . $colA;

                $currentProduct = AwProduct::updateOrCreate(
                    ['sku' => $colA],
                    [
                        'name'              => $productName,
                        'slug'              => \Illuminate\Support\Str::slug($productName) . '-' . uniqid(),
                        'product_type'      => 'simple',
                        'short_description' => $colB,
                        'status'            => 'active'
                    ]
                );

                if ($categoryId) {
                    AwProductCategory::updateOrCreate(
                        [
                            'product_id' => $currentProduct->id,
                            'category_id' => $categoryId
                        ],
                        [
                            'is_primary' => 1
                        ]
                    );
                }

                if (!empty($colE)) {
                    $unit = AwUnit::firstOrCreate(['name' => $colE]);

                    $currentUnit = AwProductUnit::updateOrCreate(
                        ['product_id' => $currentProduct->id, 'unit_id' => $unit->id, 'is_base' => 1],
                        ['quantity' => 1, 'is_default_selling' => 1]
                    );
                }

                if ($colJ) {
                    $currentSupplier = User::updateOrCreate(['code' => $colJ])->syncRoles([5]);
                }

                continue; 
            }

            // 3. IDENTIFY LOCATION DATA ROWS
            // Condition: Col A is empty, Col B has a numeric Qty, and Col C has a Location ID.
            // Also skip the "Qty available / Location" label row.
            if (empty($colA) && is_numeric($colB) && !empty($colC) && $colC !== 'Location') {
                if ($currentProduct) {
                    $warehouse = AwWarehouse::firstOrCreate(
                        ['name' => $colC],
                        [
                            'code' => \Illuminate\Support\Str::slug($colC),
                            'type' => 1,
                            'country_id' => 101, // default
                            'state_id' => 4030, // default
                            'city_id' => 57606 // default
                        ]
                    );

                    $qtyToAdd = (int) $colB;

                    $mapping = AwSupplierWarehouseProduct::updateOrCreate(
                        [
                            'product_id'   => $currentProduct->id,
                            'warehouse_id' => $warehouse->id,
                            'supplier_id' => $currentSupplier->id,
                            'unit_id' => $currentUnit->id
                        ],
                        [
                            'quantity' => \Illuminate\Support\Facades\DB::raw('quantity + ' . $qtyToAdd)
                        ]
                    );

                    // Re-fetch to get actual quantity to accurately report it if needed
                    $mapping = $mapping->fresh();

                    AwInventoryMovement::create([
                        'product_id'      => $currentProduct->id,
                        'warehouse_id'    => $warehouse->id,
                        'unit_id'         => $currentUnit->id,
                        'quantity_change' => $qtyToAdd,
                        'reason'          => 'adjustment',
                        'reference'       => 'Legacy System Import',
                        'reference_id'    => $mapping->id
                    ]);
                }
            }

            // 4. RESET WHEN TOTALS ARE REACHED
            if (str_contains(strtolower($colA), 'totals')) {
                $currentProduct = null;
            }
        }
    }
}
