<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurPORecDetails extends Model
{
    use HasFactory;

    protected $table = 'pur_pos_rec_details'; // Table name

    protected $fillable = [
        'pur_pos_rec_id',
        'product_id',
        'sku',
        'qty',
    ];

    public function receiving()
    {
        return $this->belongsTo(PurPORec::class, 'pur_pos_rec_id');
    }

    /**
     * Relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
