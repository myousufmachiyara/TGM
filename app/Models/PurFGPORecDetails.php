<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurFGPORecDetails extends Model
{
    use HasFactory;

    protected $table = 'pur_fgpos_rec_details'; // Table name

    protected $fillable = [
        'pur_fgpos_rec_id',
        'product_id',
        'variation_id',
        'sku',
        'qty',
    ];

    /**
     * Relationship with Receiving (PurFGPOReceiving)
     */
    public function receiving()
    {
        return $this->belongsTo(PurFGPORec::class, 'pur_fgpos_rec_id');
    }

    /**
     * Relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Relationship with Product Variation
     */
    public function variation()
    {
        return $this->belongsTo(ProductVariations::class, 'variation_id');
    }
}
