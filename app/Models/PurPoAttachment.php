<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurPoAttachment extends Model
{
    protected $fillable = [
        'pur_po_id',
        'att_path',
    ];

    /**
     * Relationship to the Product model
     */
    public function attachments()
    {
        return $this->belongsTo(PurPO::class);
    }
}
