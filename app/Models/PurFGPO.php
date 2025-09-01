<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurFGPO extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_fgpos';

    protected $fillable = ['doc_code', 'vendor_id', 'order_date', 'category_id'];

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function details()
    {
        return $this->hasMany(PurFGPODetails::class, 'fgpo_id');
    }

    public function voucherDetails()
    {
        return $this->hasMany(PurFGPOVoucherDetails::class, 'fgpo_id');
    }

    public function attachments()
    {
        return $this->hasMany(PurFGPOAttachements::class, 'fgpo_id');
    }

    public function challans()
    {
        return $this->hasMany(PurFgpoVoucherDetails::class, 'fgpo_id');
    }
}
