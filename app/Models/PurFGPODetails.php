<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurFGPODetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_fgpos_details';
    protected $fillable = ['fgpo_id', 'product_id', 'variation_id', 'sku', 'qty'];

    public function fgpo()
    {
        return $this->belongsTo(PurFgpo::class, 'fgpo_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariations::class, 'variation_id');
    }
}