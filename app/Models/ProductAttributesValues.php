<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttributesValues extends Model
{
    use HasFactory;

    protected $fillable = ['product_attribute_id', 'value'];

    /**
     * Relationship with ProductAttribute.
     */
    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }
}
