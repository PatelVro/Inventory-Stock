<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
    'name',
    'address',
    'barcode_number',
    'barcode_id'
];

    protected $hidden = ['created_at','updated_at'];

    public function barcode()
    {
        return $this->belongsTo(Barcode::class);
    }
    
}
