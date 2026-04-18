<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $fillable = ['transaction_id', 'status', 'notes', 'changed_by'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'received'  => 'Diterima',
            'diagnosa'  => 'Diagnosa',
            'perbaikan' => 'Perbaikan',
            'selesai'   => 'Selesai',
            'diambil'   => 'Diambil',
            default     => ucfirst($this->status),
        };
    }
}
