<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurPosDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pur_pos_id',
        'item_name',
        'category_id',
        'item_rate',
        'item_qty',
        'unit_id',
    ];

    public function purPos()
    {
        return $this->belongsTo(PurPos::class, 'pur_pos_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
