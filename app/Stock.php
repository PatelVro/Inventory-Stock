<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'product_id',
        'supplier_id',
        'qty'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
        
    ];

    /**
     * Stock belongs to a Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stock belongs to a Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
