<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'technician_id',
        'created_by',
        'status',
        'received_date',
        'completed_date',
    ];

    protected $casts = [
        'received_date'  => 'date',
        'completed_date' => 'date',
    ];

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->order_number) {
                $model->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'SVC-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detail()
    {
        return $this->hasOne(TransactionDetail::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'received'   => 'Diterima',
            'diagnosa'   => 'Diagnosa',
            'perbaikan'  => 'Perbaikan',
            'selesai'    => 'Selesai',
            'diambil'    => 'Diambil',
            'batal'      => 'Batal',
            default      => ucfirst($this->status),
        };
    }
}
