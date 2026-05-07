<?php

namespace App\Models\MonitoringTransferRak;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\MonitoringTransferRak\TransferRak;

class TransferRakDetail extends Model
{
    protected $table = 'transfer_rak_details';

    protected $fillable = [
        'transfer_rak_id',
        'id_karyawan_pengirim', // Tambahin ini
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

    public function karyawanPengirim()
    {
        return $this->belongsTo(Employee::class, 'id_karyawan_pengirim');
    }

    public function penerima()
    {
        return $this->belongsTo(Employee::class, 'id_penerima');
    }
}
