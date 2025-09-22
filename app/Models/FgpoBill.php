<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FgpoBill extends Model
{
    use HasFactory;

    protected $table = 'fgpo_bills';

    protected $fillable = [
        'bill_date',
        'vendor_id',
        'ref_bill_no',
        'total_amount',   // <-- needed (stored in controller + migration)
        'created_by',     // <-- saved in controller
    ];

    /**
     * Bill belongs to a Vendor (Chart of Account)
     */
    public function vendor()
    {
        return $this->belongsTo(ChartOfAccount::class, 'vendor_id');
    }

    /**
     * Bill has many Details
     */
    public function details()
    {
        return $this->hasMany(FgpoBillDetail::class, 'bill_id');
    }
}
