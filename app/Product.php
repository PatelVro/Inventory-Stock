<?php

namespace App;

use App\Scopes\ProductVisibilityScope;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // protected $table = 'products';

    protected $fillable = ['category_id','name','price','image','qty', 'barcode_id', 'user_id'];

    protected $hidden = ['created_at','updated_at'];

    protected static function booted()
    {
        static::addGlobalScope(new ProductVisibilityScope);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function barcode()
    {
        return $this->belongsTo(Barcode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
