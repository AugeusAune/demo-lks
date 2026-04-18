<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'complaint',
        'diagnosis',
        'repair_notes',
        'estimated_cost',
        'actual_cost',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost'    => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
