<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferRakDetail extends Model
{
    use HasFactory;

    protected $table = 'transfer_rak_details';

    protected $fillable = [
        'transfer_rak_id',
        'kode_rak',
        'waktu_scan'
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
    ];

    public function transferRak()
    {
        return $this->belongsTo(TransferRak::class);
    }
}
