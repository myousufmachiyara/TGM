<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurFgpoVoucherDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_fgpos_voucher_details';

    protected $fillable = ['fgpo_id', 'voucher_id', 'product_id', 'qty', 'rate', 'description', 'po_id', 'width'];

    public function fgpo()
    {
        return $this->belongsTo(PurFgpo::class, 'fgpo_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Jv1::class, 'voucher_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class); // Laravel can infer the foreign key
    }

    public function purPO()
    {
        return $this->belongsTo(PurPo::class);
    }
}
