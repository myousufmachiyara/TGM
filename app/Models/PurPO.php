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
        'remarks',
        'order_by',
        'order_date',
        'created_by',
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
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_id', 'id')
        ->where('account_type', 'vendor');
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
