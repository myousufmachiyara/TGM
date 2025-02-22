<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurFGPO extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_fgpos';
    protected $fillable = ['doc_code', 'vendor_id', 'order_date', 'width', 'consumption'];

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function details()
    {
        return $this->hasMany(PurFgpoDetail::class, 'fgpo_id');
    }

    public function voucherDetails()
    {
        return $this->hasMany(PurFgpoVoucherDetail::class, 'fgpo_id');
    }

    public function attachments()
    {
        return $this->hasMany(PurFgpoAttachment::class, 'fgpo_id');
    }
}
