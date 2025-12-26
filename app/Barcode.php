<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
	protected $table = "barcodes";
    protected $fillable = ['name'];

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }
}
