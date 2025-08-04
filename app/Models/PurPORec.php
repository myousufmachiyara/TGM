<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurPORec extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_pos_rec'; // Table name

    protected $fillable = [
        'po_id',
        'rec_date',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationship with the Purchase Order (PurFGPO)
     */
    public function PO()
    {
        return $this->belongsTo(PurPO::class, 'po_id');
    }

    /**
     * Relationship with Receiving Details (assuming `pur_fgpos_rec_details` table exists)
     */
    public function details()
    {
        return $this->hasMany(PurPORecDetails::class, 'pur_pos_rec_id');
    }
}
