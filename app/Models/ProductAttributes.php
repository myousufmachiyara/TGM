<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttributes extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relationship with ProductAttributeValue.
     */
    public function values()
    {
        return $this->hasMany(ProductAttributesValues::class,'product_attribute_id');
    }
}
