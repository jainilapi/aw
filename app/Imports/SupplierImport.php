<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class SupplierImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $supplierRole = Role::where('slug', 'supplier')->first();

        // Data starts from the 11th row (index 10)
        foreach ($collection->skip(10) as $row) {
            $code = isset($row[0]) ? trim($row[0]) : null;
            $name = isset($row[5]) ? trim($row[5]) : null;

            if (empty($code) || empty($name)) {
                continue;
            }

            $user = User::where('code', $code)->first();

            if ($user) {
                // Determine if this user is actually a supplier.
                // If not, we still update the name since the code matches, 
                // but typically we'd assume any code match is the same entity.
                $user->update([
                    'name' => $name
                ]);
            } else {
                // Check if name already exists as a supplier to avoid duplicates if required,
                // but usually code is the unique identifier.
                $newUser = User::create([
                    'code' => $code,
                    'name' => $name,
                    'password' => Str::random(12),
                    'status' => 1,
                    'credit_balance' => 0.00
                ]);

                if ($supplierRole) {
                    $newUser->roles()->attach($supplierRole->id);
                }
            }
        }
    }
}
