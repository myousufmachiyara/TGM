<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_id',
        'measurement_unit',
        'price',
        'sale_price',
        'purchase_note',
        'item_type'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariations::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
