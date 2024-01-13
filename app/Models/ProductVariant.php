<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    public function productVariantItems(){
        return $this->hasMany(ProductVariantItem::class,'product_variant_id', 'id');
    }
}
