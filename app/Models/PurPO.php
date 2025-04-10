<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurPO extends Model
{
    use HasFactory;

    protected $table = 'pur_pos';

    protected $fillable = [
        'vendor_id',
        'category_id',
        'po_code',
        'order_date',
        'delivery_date',
        'other_exp',
        'bill_discount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(PurPosDetail::class, 'pur_pos_id');
    }

    public function attachments()
    {
        return $this->hasMany(PurPoAttachment::class, 'pur_po_id');
    }

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }

    public function voucherDetails()
    {
        return $this->hasMany(PurFgposVoucherDetail::class, 'po_id');
    }
}
