<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurFGPOAttachements extends Model
{
    use HasFactory;

    protected $table = 'pur_fgpos_attachements';
    protected $fillable = ['fgpo_id', 'att_path'];

    public function fgpo()
    {
        return $this->belongsTo(PurFgpo::class, 'fgpo_id');
    }
}
