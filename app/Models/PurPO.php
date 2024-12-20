<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurPO extends Model
{
    use HasFactory;

    // Define the table if it doesn't follow Laravel's convention (plural form of model)
    protected $table = 'pur_pos';

    // Specify the fillable fields (columns you want to be mass-assignable)
    protected $fillable = [
        'payment_term', 
        'delivery_date', 
        'order_date',
        'vendor_name'
    ];

    // If you need to cast any fields (e.g., rate or quantity), you can use the $casts property
    protected $casts = [
        'delivery_date' => 'date', // Ensures that delivery_date is treated as a date
    ];

    public function details()
    {
        return $this->hasMany(PurPosDetail::class, 'pur_pos_id');
    }
}
