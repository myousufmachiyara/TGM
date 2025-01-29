<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurPO extends Model
{
    use HasFactory;

    protected $table = 'pur_pos';

    protected $fillable = [
        'delivery_date', 
        'order_date',
        'vendor_name',
        'other_exp',
        'bill_discount'
    ];

    protected $casts = [
        'order_date' => 'date', 
        'delivery_date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(PurPosDetail::class, 'pur_pos_id');
    }

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_name', 'id'); 
    }

}
