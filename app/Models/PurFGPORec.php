<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurFGPORec extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pur_fgpos_rec'; // Table name

    protected $fillable = [
        'fgpo_id',
        'rec_date',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationship with the Purchase Order (PurFGPO)
     */
    public function FGPO()
    {
        return $this->belongsTo(PurFGPO::class, 'fgpo_id');
    }

    /**
     * Relationship with Receiving Details (assuming `pur_fgpos_rec_details` table exists)
     */
    public function details()
    {
        return $this->hasMany(PurFGPORecDetails::class, 'pur_fgpos_rec_id');
    }
}
