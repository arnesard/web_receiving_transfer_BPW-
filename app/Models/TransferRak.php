<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRak extends Model
{
    protected $table = 'transfer_rak';

    protected $fillable = [
        'operator_id',
        'nama_driver',
        'no_mobil',
        'waktu_mulai',
        'waktu_selesai'
    ];

    public function details()
    {
        return $this->hasMany(TransferRakDetail::class, 'transfer_rak_id');
    }
}
