<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_Out extends Model
{
    protected $table = 'product_out';

    protected $fillable = ['product_id','supplier_id','qty','date'];

    protected $hidden = ['created_at','updated_at'];

    public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}

public function supplier()
{
    return $this->belongsTo(Supplier::class, 'supplier_id');
}
}
