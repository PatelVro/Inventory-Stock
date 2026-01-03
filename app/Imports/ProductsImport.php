<?php

namespace App\Imports;

use App\Product;
use App\Barcode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Create or find barcode
        $barcode = Barcode::firstOrCreate(['name' => $row['barcode']]);

        return new Product([
            'name'        => $row['name'],
            'barcode_id'  => $barcode->id,
            'price'       => $row['price'],
            'qty'         => $row['qty'],
            'category_id' => $row['category_id'],
        ]);
    }
}
