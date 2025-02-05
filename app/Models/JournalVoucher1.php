<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalVoucher1 extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jv1'; // Specify the table name if it's different from Laravel's convention

    protected $fillable = [
        'debit_acc_id',
        'credit_acc_id',
        'amount',
        'date',
        'narration',
        // 'created_by',
        // 'updated_by'
    ];

    public function debitAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'debit_acc_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'credit_acc_id');
    }

    // public function createdByUser()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }

    // public function updatedByUser()
    // {
    //     return $this->belongsTo(User::class, 'updated_by');
    // }
}
