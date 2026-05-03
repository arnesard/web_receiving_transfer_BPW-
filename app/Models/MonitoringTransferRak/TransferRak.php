<?php

namespace App\Models\MonitoringTransferRak;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class TransferRak extends Model
{
    protected $table = 'transfer_raks';

    protected $fillable = [
        'user_id',
        'id_karyawan',
        'id_supir',
        'id_mobil',
        'lokasi_asal',
        'lokasi_tujuan',
        'id_karyawan_penerima',
        'total_rak',
        'waktu_mulai',
        'waktu_selesai',
        'waktu_diterima',
        'status',
        'catatan',
    ];

    protected $casts = [
        'waktu_mulai'    => 'datetime',
        'waktu_selesai'  => 'datetime',
        'waktu_diterima' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TransferRakDetail::class, 'transfer_rak_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Employee::class, 'id_karyawan');
    }

    public function penerima()
    {
        return $this->belongsTo(Employee::class, 'id_karyawan_penerima');
    }

    public function supir()
    {
        return $this->belongsTo(Driver::class, 'id_supir');
    }

    public function mobil()
    {
        return $this->belongsTo(Vehicle::class, 'id_mobil');
    }
}
