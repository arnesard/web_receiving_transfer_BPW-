<?php

namespace App\Models\MonitoringTransferRak;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class TransferRakDetail extends Model
{
    protected $table = 'transfer_rak_details';

    protected $fillable = [
        'transfer_rak_id',
        'kode_rak',
        'waktu_scan',
        'lokasi_diterima',
        'id_penerima',
        'waktu_diterima',
    ];

    protected $casts = [
        'waktu_scan'     => 'datetime',
        'waktu_diterima' => 'datetime',
    ];

    public function transferRak()
    {
        return $this->belongsTo(TransferRak::class, 'transfer_rak_id');
    }

    public function penerima()
    {
        return $this->belongsTo(Employee::class, 'id_penerima');
    }
}
