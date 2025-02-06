<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariations extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'variation_value_id',
        'sku',
        'price',
        'stock',
        'attribute_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function values()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variation_values', 'variation_id', 'value_id');
    }
}
