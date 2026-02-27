<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use App\Models\ProductImport as ModelsProductImport;
use ZipArchive;

class ProductImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (ModelsProductImport::where('status', 'pending')->get() as $row) {
            if ($row->type == 2) {
                if (is_file(public_path("storage/inventory-imports/{$row->file}")) && file_exists(public_path("storage/inventory-imports/{$row->file}"))) {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'in-queue'
                    ]);
                    $this->importInventories($row);
                } else {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'failed',
                        'error_reason' => 'File does not exists'
                    ]);
                }
            } else if ($row->type == 1) {
                if (is_file(public_path("storage/product-images/{$row->file}")) && file_exists(public_path("storage/product-images/{$row->file}"))) {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'in-queue'
                    ]);
                    $this->importImages($row);
                } else {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'failed',
                        'error_reason' => 'File does not exists'
                    ]);
                }
            } else if ($row->type == 3) {
                if (is_file(public_path("storage/supplier-imports/{$row->file}")) && file_exists(public_path("storage/supplier-imports/{$row->file}"))) {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'in-queue'
                    ]);
                    $this->importSuppliers($row);
                } else {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'failed',
                        'error_reason' => 'File does not exists'
                    ]);
                }
            } else {
                if (is_file(public_path("storage/product-imports/{$row->file}")) && file_exists(public_path("storage/product-imports/{$row->file}"))) {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'in-queue'
                    ]);
                    $this->importProducts($row);
                } else {
                    ModelsProductImport::find($row->id)->update([
                        'status' => 'failed',
                        'error_reason' => 'File does not exists'
                    ]);
                }
            }
        }
    }

    public function importImages($row) {
        $zipPath = public_path("storage/product-images/{$row->file}");
        $extractPath = storage_path('app/public/temp-products');

        if (!Storage::disk('public')->exists('temp-products')) {
            Storage::disk('public')->makeDirectory('temp-products');
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();

            $files = scandir($extractPath);
            $processedSkus = [];

            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }

                $filePath = $extractPath . '/' . $file;
                
                $pathInfo = pathinfo($file);
                $filename = $pathInfo['filename'];
                $extension = $pathInfo['extension'] ?? '';

                if (empty($extension) || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])) {
                    continue;
                }

                $lastUnderscorePos = strrpos($filename, '_');
                if ($lastUnderscorePos === false) {
                     unlink($filePath);
                     continue;
                }

                $sku = substr($filename, 0, $lastUnderscorePos);
                $index = substr($filename, $lastUnderscorePos + 1);

                if (!is_numeric($index)) {
                    unlink($filePath);
                    continue;
                }
                
                $isPrimary = ($index == 0) ? 1 : 0;
                
                $product = \App\Models\Product::where('sku', $sku)->first();

                if ($product) {
                    if ($row->override && !in_array('P_' . $product->id, $processedSkus)) {
                        $secondaryImages = \App\Models\ProductImage::where('product_id', $product->id)->where('is_primary', 0)->get();
                        foreach($secondaryImages as $img) {
                             $img->delete();
                        }
                        $processedSkus[] = 'P_' . $product->id;
                    }
                    
                    if ($isPrimary) {
                         \App\Models\ProductImage::where('product_id', $product->id)->where('is_primary', 1)->update(['is_primary' => 0]);
                    }

                    $newFileName = uniqid() . '.' . $extension;
                    if (!Storage::disk('public')->exists('products')) {
                        Storage::disk('public')->makeDirectory('products');
                    }
                    
                    rename($filePath, public_path("storage/products/{$newFileName}"));

                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'is_primary' => $isPrimary,
                        'file' => "products/{$newFileName}"
                    ]);

                } else {
                    $variant = \App\Models\ProductVariant::where('sku', $sku)->first();

                    if ($variant) {
                        if ($row->override && !in_array('V_' . $variant->id, $processedSkus)) {
                            $secondaryImages = \App\Models\ProductVariantImage::where('variant_id', $variant->id)->where('is_primary', 0)->get();
                             foreach($secondaryImages as $img) {
                                 $img->delete();
                            }
                            $processedSkus[] = 'V_' . $variant->id;
                        }

                        if ($isPrimary) {
                             \App\Models\ProductVariantImage::where('variant_id', $variant->id)->where('is_primary', 1)->update(['is_primary' => 0]);
                        }

                        $newFileName = uniqid() . '.' . $extension;
                        if (!Storage::disk('public')->exists('products')) {
                            Storage::disk('public')->makeDirectory('products');
                        }
                        
                        rename($filePath, public_path("storage/products/{$newFileName}"));

                        \App\Models\ProductVariantImage::create([
                            'product_id' => $variant->product_id,
                            'variant_id' => $variant->id,
                            'is_primary' => $isPrimary,
                            'file' => "products/variants/{$newFileName}"
                        ]);

                    } else {
                         if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }
                        
            ModelsProductImport::find($row->id)->update([
                'status' => 'imported'
            ]);

        } else {
            ModelsProductImport::find($row->id)->update([
                 'status' => 'failed',
                 'error_reason' => 'Unable to open ZIP file'
            ]);
        }
    }

    public function importProducts($row) {
        \Maatwebsite\Excel\Facades\Excel::import(
            new \App\Imports\SimpleProduct($row->id), 
            public_path("storage/product-imports/{$row->file}")
        );
    }

    public function importInventories($row) {
        \Maatwebsite\Excel\Facades\Excel::import(
            new \App\Imports\InventoryImport, 
            public_path("storage/inventory-imports/{$row->file}")
        );
    }

    public function importSuppliers($row) {
        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\SupplierImport, 
                public_path("storage/supplier-imports/{$row->file}")
            );
            
            ModelsProductImport::find($row->id)->update([
                'status' => 'imported'
            ]);
        } catch (\Exception $e) {
            ModelsProductImport::find($row->id)->update([
                'status' => 'failed',
                'error_reason' => $e->getMessage()
            ]);
        }
    }
}
